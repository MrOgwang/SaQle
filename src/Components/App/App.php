<?php

namespace SaQle\Components\App;

use SaQle\Http\Response\Message;
use SaQle\Routes\Resources\ResourceRouteUtils;
use SaQle\Core\Support\Index;

class App {

     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(){
         $this->__utilsConstruct();
     }

     private function import(){
         $model_class = Candidate::class; //config('auth.model_class');

         $old = $model_class::using('main.acek_older')->get()->order(['created_at'], 'DESC')->all();
         $added = [];

         foreach($old as $o){
             $added[] = $model_class::using('main.acek')->create($o->get_data())->now();
         }
     }

     #[Index]
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
             'current_resource' => $current_resource,
             'tenant_slug' => $this->tenant_slug,
             'auth_context' => auth_context()
         ]);
     }
}
