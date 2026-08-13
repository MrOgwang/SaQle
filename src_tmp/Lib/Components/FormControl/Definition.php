<?php

namespace SaQle\Lib\Components\FormControl;

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\Request;

final class Definition extends ComponentDefinition {

     private function make_file_name(string $type) : string {

         $file_name = ucwords(str_replace(['_', '-'], " ", $type));

         $file_name = str_replace(" ", "", $file_name);

         return $file_name;
     }

     public function template(Request $request, ...$args): ?string {

         $type = $args['field']->type;

         $name = $this->make_file_name($type);

         $path = path_join([
             $this->path('Templates'),
             $name.".".config('app.component_template_ext')
         ]);

         if(file_exists($path)){
             return $name;
         }

         return match($type){
             'datetime-local' => 'DateTimeLocal',
             'textarea'       => 'TextArea',
             default          => null
         };
     }
}