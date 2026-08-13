<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Support\Cli;
use SaQle\Core\Support\ResolverComponent;
use SaQle\Core\Registries\ComponentRegistry;
use ReflectionClass;
use RuntimeException;

class ComponentCompiler {

     private static array $components_dirs = [];

     protected static function normalize_path(string $path){
        
         $owner = "project";

         if(str_starts_with($path, config('base_path'))){
             $path = str_replace(config('base_path').DIRECTORY_SEPARATOR, '', $path);
             $owner = "project";
         }elseif(str_starts_with($path, config('framework_path'))){
             $path = str_replace(config('framework_path').DIRECTORY_SEPARATOR, '', $path);
             $owner = "framework";
         }
         
         $path = str_replace('\\', '/', $path); // normalize slashes

         return [$path, $owner];
     }

     private static function initialize_component(): array {

         return [
             'controller'             => '',
             'controller_path'        => '',

             'definition'             => null,
             'definition_path'        => null,

             'template_path'          => '',
             'compiled_template_path' => '',

             'style_path'             => null,
             'script_path'            => null,

             'owner'                  => null,
             'proxy'                  => false,

             'has_many_templates'     => false,
             'template_variations'    => [],
         ];
     }

     private static function cache_components(array $components): void {

         $caching_folder = path_join([config('base_path'), config('class_mappings_dir')]);

         if(!is_dir($caching_folder)){
             mkdir($caching_folder, 0777, true);
         }

         $caching_file = path_join([$caching_folder, 'components.php']);

         $export = var_export($components, true);

         $export = preg_replace('/^/m', '    ', $export);

         $php_content ="<?php\n\n"."return " .$export.";\n";

         file_put_contents($caching_file, $php_content);
     }

     /**
     * Add a component source directory.
     *
     * Every direct child directory is considered a component.
     */
     private static function add_components_dir(string $path, string $prefix) : void {
         self::$components_dirs[] = [
             'path'   => $path,
             'prefix' => $prefix
         ];
     }

     /**
     * Build all component source directories.
     */
     private static function initialize_sources() : void {

         self::$components_dirs = [];

         // Project components.
         self::add_components_dir(
             path_join([config('base_path'), 'Components']),
             'app'
         );

         // Framework module components.
         foreach (config('framework_modules') as $fm){
             $module = new $fm();

             self::add_components_dir(
                 $module->path('Components'),
                 'saqle.'.strtolower((new ReflectionClass($fm))->getShortName())
             );
         }

         // Application module components.
         foreach (config('app.modules') as $am){
             $module = new $am();

             self::add_components_dir(
                 $module->path('Components'),
                 'app.'.strtolower((new ReflectionClass($am))->getShortName())
             );
         }

         //Additional component directories.
         foreach (config('app.extra_components_dirs') as $directory) {
             self::add_components_dir(
                 path_join([config('base_path'), $directory]),
                 'app'
             );
         }
     }

     /**
     * Compile components.
     */
     public static function compile(): void {

         Cli::print("Compiling components...");

         self::initialize_sources();

         $components = [];

         foreach (self::$components_dirs as $source){

             $path   = $source['path'];
             $prefix = $source['prefix'];

             if(!is_dir($path)){
                 continue;
             }

             //Every direct directory is a component.
             foreach(self::component_directories($path) as $component_path){

                 $component = self::compile_component($component_path, $prefix);

                 $component_name = strtolower($prefix.'.'.basename($component_path));

                 if(isset($components[$component_name])){
                     throw new RuntimeException("Duplicate component: {$component_name}");
                 }

                 $components[$component_name] = $component;
             }
         }

         self::cache_components($components);

         Cli::print("Components compiled and cached\n");
     }

     /**
     * Get direct component directories.
     */
     private static function component_directories(string $path) : array {

         $directories = [];

         foreach(scandir($path) ?: [] as $directory){

             if($directory === '.' || $directory === '..'){
                 continue;
             }

             $component_path = path_join([$path, $directory]);

             if(is_dir($component_path)){
                 $directories[] = $component_path;
             }
         }

         return $directories;
     }

     /**
     * Compile one component directory.
     */
     private static function compile_component(string $component_path, string $owner): array {

         $component_name = basename(rtrim($component_path, DIRECTORY_SEPARATOR));

         Cli::print("\nComponent: $component_name");

         $component = self::initialize_component();

         $component_owner = "project";

         //controller
         $controller_path = path_join([$component_path, 'Controller.php']);

         if(is_file($controller_path)){

             [$compile_path, $file_owner] = self::normalize_path(realpath($controller_path));

             $component_owner = $file_owner;

             $component['controller_path'] = $compile_path;

             //Extract class information.
             $content = file_get_contents($controller_path);

             preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);

             preg_match('/class\s+(\w+)/', $content, $class_match);

             $namespace = $namespace_match[1] ?? null;

             $class_name = $class_match[1] ?? null;

             if($namespace && $class_name){

                 $fqcn = $namespace.'\\'.$class_name;

                 $component['controller'] = $fqcn;

                 if(is_a($fqcn, ResolverComponent::class, true)){
                     $component['proxy'] = true;
                 }

                 Cli::print("Controller: $fqcn");
             }
         }

         //Definition
         $definition_path = path_join([$component_path, 'Definition.php']);

         if(is_file($definition_path)){

             [$compile_path, $file_owner] = self::normalize_path(realpath($definition_path));

             $component_owner = $file_owner;

             $component['definition_path'] = $compile_path;

             $content = file_get_contents($definition_path);

             preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);

             preg_match('/class\s+(\w+)/', $content, $class_match);

             $namespace = $namespace_match[1] ?? null;

             $class_name = $class_match[1] ?? null;

             if($namespace && $class_name){

                 $def = $namespace.'\\'.$class_name;

                 $component['definition'] = $def;

                 Cli::print("Definition: $def");
             }
         }

         //Default template
         $template_path = path_join([$component_path, 'Template.html']);

         if(is_file($template_path)){

             [$compile_path, $file_owner] = self::normalize_path(realpath($template_path));

             $component_owner = $file_owner;

             $component['template_path'] = $compile_path;

             $component['compiled_template_path'] = self::compile_template($template_path, $file_owner);

             /*
             * The default template is also
             * available as the component variation.
             */
             $component['template_variations'][strtolower($component_name)] = [
                 'template_path' => $compile_path,
                 'compiled_template_path' => $component['compiled_template_path']
             ];

             Cli::print("Template: $compile_path");
         }

         //css
         $style_path = path_join([$component_path, 'Style.css']);

         if(is_file($style_path)){
             [$compile_path, $file_owner] = self::normalize_path(realpath($style_path));

             $component_owner = $file_owner;

             $component['style_path'] = $compile_path;

             Cli::print("Style: $compile_path");
         }

         //JavaScript
         $script_path = path_join([$component_path, 'Script.js']);

         if(is_file($script_path)){

             [$compile_path, $file_owner] = self::normalize_path(realpath($script_path));

             $component_owner = $file_owner;

             $component['script_path'] = $compile_path;

             Cli::print("Script: $compile_path");
         }

         //Template variations
         $templates_path = path_join([$component_path, 'Templates']);

         if(is_dir($templates_path)){

             Cli::print("Templates: $compile_path");

             foreach(self::template_files($templates_path) as $variation => $variation_path){

                 [$compile_path, $file_owner] = self::normalize_path(realpath($variation_path));

                 $component_owner = $file_owner;

                 $component['template_variations'][strtolower($variation)] = [
                     'template_path' => $compile_path,
                     'compiled_template_path' => self::compile_template($variation_path, $file_owner)
                 ];

                 Cli::print("$variation: $compile_path");
             }
         }

         //Finalize
         $variation_count = count($component['template_variations']);

         /*
         * A component has many templates when
         * it has more than one template available.
         */
         $component['has_many_templates'] = $variation_count > 1;

         $component['owner'] = $component_owner;

         Cli::print("Component compilation successful!\n");

         return $component;
     }

     /**
     * Find templates inside Templates/.
     *
     * FileOne.html -> FileOne
     * FileTwo.html -> FileTwo
     */
     private static function template_files(string $path) : array {

         $templates = [];

         foreach (scandir($path) ?: [] as $filename){

             if($filename === '.' || $filename === '..'){
                 continue;
             }

             $template_path = path_join([$path, $filename]);

             if(!is_file($template_path)){
                 continue;
             }

             if(strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'html'){
                 continue;
             }

             $variation = pathinfo($filename, PATHINFO_FILENAME);

             $templates[strtolower($variation)] = $template_path;
         }

         return $templates;
     }

     /**
     * Compile a template.
     */
     private static function compile_template(string $template_path, string $owner): string {
         return TemplateCompiler::compile_template($template_path);
     }
}