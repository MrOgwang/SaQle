<?php
namespace SaQle\Build\Commands;

use Exception;

final class MakeComponent {
     public static function execute(string $name, ?string $module = null, bool $proxy = false){
         
         $name_slug = self::slug($name);
         $module_slug = $module ? self::slug($module, "Module") : "";

         if($module){
             $base_path = base_path("modules", strtolower($module_slug), "components");
             $namespace = "App\\Modules\\".ucwords($module_slug)."\\Components\\".ucwords($name_slug);
         }else{
             $base_path = base_path("components");
             $namespace = "App\\Components\\".ucwords($name_slug);
         }

         $component_path = $base_path."/".$name_slug;

         if(is_dir($component_path)){
             cli_log("Component already exists.\n");
             return;
         }

         mkdir($component_path, 0777, true);

         self::create_php($component_path, $name_slug, $namespace, $proxy);
         if(!$proxy){
             self::create_html($component_path, $name_slug);
             self::create_css($component_path, $name_slug);
             self::create_js($component_path, $name_slug);
             self::create_json($component_path, $name_slug);
         }

         cli_log("Component {$name} created successfully.\n");
     }

     private static function slug($name, string $type = "Component"){
         if(!preg_match('/^[A-Za-z_]+$/', $name)){
             throw new Exception("{$type} name can only contain letters and underscore.");
         }

         return strtolower($name);
     }

     private static function create_php($path, $slug, $namespace, $proxy){
         $class = ucfirst($slug);

         if(!$proxy){
         $content = <<<PHP
<?php

namespace {$namespace};

class {$class} {
     public function get() {
        return ok([]);
     }
}

PHP;
     }else{
        $content = <<<PHP
<?php

namespace {$namespace};

use SaQle\Core\Support\ResolverComponent;

class {$class} extends ResolverComponent {
     public function get_component() : string {
        return "";
     }
}

PHP;
     }

         file_put_contents("{$path}/{$slug}.php", $content);
     }

     private static function create_html($path, $slug){
         $content = <<<HTML
<div class="{$slug}">
     <p>{$slug} component</p>
</div>
HTML;

         file_put_contents("{$path}/{$slug}.html", $content);
     }

     private static function create_css($path, $slug) {
         $content = <<<CSS
.{$slug} {

}
CSS;

         file_put_contents("{$path}/{$slug}.css", $content);
     }

     private static function create_js($path, $slug){
        $content = <<<JS
document.addEventListener("DOMContentLoaded", function () {

});
JS;

         file_put_contents("{$path}/{$slug}.js", $content);
     }

     private static function create_json($path, $slug){
         $content = <<<JSON
{
    "dependencies": {
        "css": [],
        "js": []
    }
}
JSON;

         file_put_contents("{$path}/{$slug}.json", $content);
     }
}
