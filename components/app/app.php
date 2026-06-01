<?php

namespace SaQle\Components\App;

use SaQle\Http\Response\Message;
use SaQle\Core\Registries\ModelRegistry;

class App {
     private function get_resource_links(){

         $links = [];

         //get developer defined db schemas
         $db_schemas = config('db.schemas');

         foreach($db_schemas as $schema_name => $schema_class){
             $models = new $schema_class()->get_developer_models();

             foreach($models as $model_label => $model_class){

                 $links[$model_class] = (Object)[
                     'url' => '/_auto/'.$model_label, 
                     'plural_label' => ucwords($model_label),
                     'singular_label' => ModelRegistry::get_model_name($model_class)
                 ];
             }
         }

         return $links;
     }

     public function get(){
         $resources = $this->get_resource_links();

         return Message::ok([
             'resources' => $resources,
             'current_resource' => $resources[request()->route->model_class] ?? null
         ]);
     }
}
