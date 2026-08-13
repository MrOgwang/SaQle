<?php
namespace SaQle\Build\Commands;

use Exception;
use SaQle\Core\Support\Cli;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

final class MakeComponent extends Command {

     public function signature(): Signature {
         return Signature::make()
         ->argument(
             name: 'name',
             required: true,
             description: 'The name of the component to create'
         )
         ->argument(
             name: 'module',
             default: "",
             required: false,
             description: 'The module to which the component belongs'
         )
         ->flag(
             name: 'proxy',
             shortcut: '-p',
             description: 'Whether this is a proxy component or not'
         );
     }

     public function handle(CommandContext $context) : int {

         $name = $context->argument('name');
         $module = $context->argument('module');
         $proxy  = $context->option('proxy', false);

         $name_slug = self::slug($name);
         $module_slug = $module ? self::slug($module, "Module") : "";

         if($module){
             $base_path = base_path("modules", $module_slug, "Components");
             $namespace = "App\\Modules\\".ucwords($module_slug)."\\Components\\".ucwords($name_slug);
         }else{
             $base_path = base_path("Components");
             $namespace = "App\\Components\\".ucwords($name_slug);
         }

         $component_path = $base_path."/".$name_slug;

         if(is_dir($component_path)){
             Cli::print("Component already exists.\n");
             return 0;
         }

         mkdir($component_path, 0777, true);

         self::create_php($component_path, $name_slug, $namespace, $proxy);

         if(!$proxy){
             self::create_html($component_path, $name_slug);
             self::create_css($component_path, $name_slug);
             self::create_js($component_path, $name_slug);
             self::create_definition($component_path, $namespace);
         }

         Cli::print("Component {$name} created successfully.\n");

         return 0;
     }

     private static function slug($name, string $type = "Component"){
         if(!preg_match('/^[A-Za-z_]+$/', $name)){
             throw new Exception("{$type} name can only contain letters and underscore.");
         }

         return $name;
     }

     private static function create_php($path, $slug, $namespace, $proxy){
         $class = ucfirst($slug);

         if(!$proxy){
         $content = <<<PHP
<?php

namespace {$namespace};

use SaQle\Http\Response\Message;

class Controller {
     public function get() {
        return Message::ok();
     }
}

PHP;
     }else{
        $content = <<<PHP
<?php

namespace {$namespace};

use SaQle\Core\Support\ResolverComponent;

class Controller extends ResolverComponent {
     public function get_component() : string {
        return "";
     }
}

PHP;
     }

         file_put_contents("{$path}/Controller.php", $content);
     }

     private static function create_html($path, $slug){
         $content = <<<HTML
<div class="{$slug}">
     <p>{$slug} component</p>
</div>
HTML;

         file_put_contents("{$path}/Template.html", $content);
     }

     private static function create_css($path, $slug) {
         $content = <<<CSS
.{$slug} {

}
CSS;

         file_put_contents("{$path}/Style.css", $content);
     }

     private static function create_js($path, $slug){
        $content = <<<JS
document.addEventListener("DOMContentLoaded", function () {

});
JS;

         file_put_contents("{$path}/Style.js", $content);
     }

     private static function create_definition($path, $namespace){
         $content = <<<PHP
<?php

namespace {$namespace};

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\Request;

class Definition {

     public function template(Request \$request, ...\$args): ?string {
         return null;
     }

     public function dependencies() : array {
         return [];
     }

}

PHP;

         file_put_contents("{$path}/Definition.php", $content);
     }
}
