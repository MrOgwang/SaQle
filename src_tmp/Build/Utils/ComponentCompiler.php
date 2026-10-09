<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Support\Cli;
use SaQle\Core\Support\ResolverComponent;
use SaQle\Core\Registries\ComponentRegistry;
use SaQle\Core\Components\Component;
use ReflectionClass;
use RuntimeException;

class ComponentCompiler {

     private static array $components_dirs = [];

     protected static function normalize_path(string $path) : string {
        
         if(str_starts_with($path, config('base_path'))) {
             $path = str_replace(config('base_path').DIRECTORY_SEPARATOR, '', $path);
         }elseif(str_starts_with($path, config('framework_path'))){
             $path = str_replace(config('framework_path').DIRECTORY_SEPARATOR, '', $path);
         }
         
         return str_replace('\\', '/', $path); // normalize slashes
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

     //Build all component source directories.
     private static function initialize_sources() : void {

         self::$components_dirs = [];

         //Framework module components.
         foreach (config('framework_modules') as $fm){
             $module = new $fm();

             self::add_components_dir(
                 $module->path('Components'),
                 'saqle.'.strtolower((new ReflectionClass($fm))->getShortName())
             );
         }

         //Application module components.
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

     //Compile components.
     public static function compile(): void {

         Cli::print("Compiling components...");

         self::initialize_sources();

         $components = [];

         foreach(self::$components_dirs as $dir){

             $path   = $dir['path'];
             $prefix = $dir['prefix'];

             if(!is_dir($path)){
                 continue;
             }

             //Every direct directory is a component.
             foreach(self::component_directories($path) as $component_path){

                 /**
                  * Component names are constructed this way: namespace.module.component
                  * 
                  * Namespace is app or saqle, depending on whether a component is project defined
                  * or framework defined
                  * */
                 $name = strtolower($prefix.'.'.basename(rtrim($component_path, DIRECTORY_SEPARATOR)));

                 $component = self::compile_component(new Component(name: $name), $component_path);

                 if(isset($components[$name])){
                     throw new RuntimeException("Duplicate component: {$name}");
                 }

                 $components[$name] = $component->to_array();
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

     private static function get_class_and_namespace(string $content) : array {

         preg_match('/namespace\s+([^;]+);/', $content, $namespace_match);

         preg_match('/class\s+(\w+)/', $content, $class_match);

         return [$class_match[1] ?? null, $namespace_match[1] ?? null];

     }

     private static function update_namespace(Component $c, string $path, string $content) : ?string {

         if(!$c->has_override()){
            return null;
         }

         $namespace = "App\\Components\\".$c->get_override_dir();

         $content = preg_replace(
             '/^(\s*namespace\s+)[^;]+;/m',
             '$1'.$namespace.';',
             $content,
             1
         );

         file_put_contents($path, $content);

         return $namespace;

     }

     private static function compile_component_controller(Component $component, string $component_path){

         $controller_path = path_join([$component_path, 'Controller.php']);

         if(!is_file($controller_path)){
             return $component;
         }

         $component->controller_path(self::normalize_path(realpath($controller_path)));

         $content = file_get_contents($controller_path);

         [$class, $namespace] = self::get_class_and_namespace($content);

         if(!$class || !$namespace){
             return $component;
         }

         $_namespace = self::update_namespace($component, $controller_path, $content);
         if($_namespace){
             $namespace = $_namespace;
         }

         $fqcn = $namespace.'\\'.$class;

         $component->controller($fqcn);

         if(is_a($fqcn, ResolverComponent::class, true)){
             $component['proxy'] = true;
         }

         Cli::print("Controller: $fqcn");

         return $component;
     }

     private static function compile_component_definition(Component $component, string $component_path){
         
         $definition_path = path_join([$component_path, 'Definition.php']);

         if(!is_file($definition_path)){
             return $component;
         }

         $component->definition_path(self::normalize_path(realpath($definition_path)));

         $content = file_get_contents($definition_path);

         [$class, $namespace] = self::get_class_and_namespace($content);

         if(!$class || !$namespace){
             return $component;
         }

         $_namespace = self::update_namespace($component, $definition_path, $content);
         if($_namespace){
             $namespace = $_namespace;
         }

         $fqcn = $namespace.'\\'.$class;

         $component->definition($fqcn);

         Cli::print("Definition: $fqcn");

         return $component;
     }

     private static function compile_component_template(Component $component, string $component_path){

         $template = path_join([$component_path, 'Template.html']);

         if(!is_file($template)){
             return $component;
         }

         $component->template_path(self::normalize_path(realpath($template)));

         $component->compiled_template_path(self::compile_template($template, $component->get_name()));

         $nameparts = explode('.', $component->get_name());
        
         $component->add_template_variation(
             strtolower(end($nameparts)),
             $component->get_template_path(),
             $component->get_compiled_template_path()
         );

         Cli::print("Template: ".$component->get_template_path());

         return $component;
     }

     private static function compile_component_style(Component $component, string $component_path){
         
         $style = path_join([$component_path, 'Style.css']);

         if(!is_file($style)){
             return $component;
         }

         $component->style_path(self::normalize_path(realpath($style)));

         Cli::print("Style: ".$component->get_style_path());

         return $component;

     }

     private static function compile_component_script(Component $component, string $component_path){
         
         $script = path_join([$component_path, 'Script.js']);

         if(!is_file($script)){
             return $component;
         }

         $component->script_path(self::normalize_path(realpath($script)));

         Cli::print("Script: ".$component->get_script_path());

         return $component;
     }

     private static function compile_component_templates(Component $component, string $component_path){
         
         $templates = path_join([$component_path, 'Templates']);

         if(!is_dir($templates)){
             return $component;
         }

         Cli::print("Template variations!\n");

         foreach(self::template_files($templates) as $variation => $variation_path){

             $normalize_path = self::normalize_path(realpath($variation_path));

             $component->add_template_variation(
                 strtolower($variation),
                 $normalize_path,
                 self::compile_template(
                     $variation_path, 
                     $component->get_name()."_".strtolower($variation)
                 )
             );

             Cli::print("$variation: $normalize_path");
         }

         return $component;

     }

     //Compile one component
     private static function compile_component(Component $component, string $component_path): Component {

         Cli::print("\nComponent: ".$component->get_name());

         $existing_component = ComponentRegistry::get($component->get_name());

         $component_path = $component->has_override() ? $component->get_override_path() : $component_path;

         $base_path = self::normalize_path(realpath($component_path));

         //set component paths
         $component->path($base_path);
         $component->base_path($base_path);
         $component->source_path(!$existing_component ? $base_path : $existing_component['source_path']);

         //set component ownership
         if($existing_component && isset($existing_component['source_owner'])){
             $component->source_owner($existing_component['source_owner']);
         }

         //controller 
         $component = self::compile_component_controller($component, $component_path);

         //Definition
         $component = self::compile_component_definition($component, $component_path);
         
         //Default template
         $component = self::compile_component_template($component, $component_path);
         
         //style
         $component = self::compile_component_style($component, $component_path);

         //script
         $component = self::compile_component_script($component, $component_path);

         //Template variations
         $component = self::compile_component_templates($component, $component_path);

         Cli::print("Component compilation successful!\n");

         return $component;
     }

     /**
     * Find templates inside Templates/.
     *
     * FileOne/Template.html -> fileone
     * FileTwo/Template.html -> filetwo
     */
     private static function template_files(string $path) : array {

         $templates = [];

         foreach(scandir($path) ?: [] as $directory){

             if($directory === '.' || $directory === '..'){
                 continue;
             }

             $template_directory = path_join([$path, $directory]);

             if(!is_dir($template_directory)){
                 continue;
             }

             $template_path = path_join([$template_directory, 'Template.html']);

             if(!is_file($template_path)){
                 continue;
             }

             $variation = $directory;

             $templates[strtolower($variation)] = $template_path;
         }

         return $templates;
     }

     /**
     * Compile a template.
     */
     private static function compile_template(string $template_path, string $template_name): string {
         return TemplateCompiler::compile_template(
             template_path: $template_path, 
             template_name: $template_name
         );
     }
}