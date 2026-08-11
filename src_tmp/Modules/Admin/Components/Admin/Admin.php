<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Http\Response\Message;
use SaQle\Routing\Resources\ResourceRouteUtils;
use SaQle\Core\Support\Index;
use SaQle\Auth\Context\ActorContext;
use SaQle\Admin\Admin as AdminProvider;

class Admin {

     use ResourceRouteUtils {
         ResourceRouteUtils::__construct as private __utilsConstruct;
     }

     public function __construct(){
         $this->__utilsConstruct();
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

         $is_platform = ActorContext::is_platform();

         return Message::ok([
             'resources' => $resources,
             'current_resource' => $current_resource,
             'tenant_slug' => $this->tenant_slug,
             'is_platform' => $is_platform,
             'navigation' => AdminProvider::navigation(),
             'tenant_name' => config('tenancy.enabled') && request()->tenant ? request()->tenant->tenant_name : ""
         ]); 
     }
} 
