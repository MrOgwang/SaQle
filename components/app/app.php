<?php

namespace SaQle\Components\App;

use SaQle\Http\Response\Message;
use SaQle\Routes\Resources\ResourceRouteUtils;

class App {

     use ResourceRouteUtils;

     public function get(){

         $resources = $this->get_resource_links();

         $route_model = request()->route->model_class;
         $model_class = "";

         if($route_model){
             $model_parts = explode("@", $route_model);
             $model_class = $model_parts[0] ?? "";
         }

         $current_resource = $resources[$model_class] ?? null;

         return Message::ok([
             'resources' => $resources,
             'current_resource' => $current_resource
         ]);
     }
}
