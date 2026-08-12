<?php

 use SaQle\Admin\{
     Admin,
     Platform
 };
 use SaQle\Routing\Router;

 function construct_route_authorization($model_label, $model_class){

     $global = trim(config('admin.authorization.global', ""));

     $route  = trim(config('admin.authorization.resources', [])[$model_label] ?? "");

     if($global && $route){
         return $global." && ".$route;
     }

     if($global && !$route){
         return $global;
     }

     return $route;
 }

 function construct_route_middleware($model_label, $model_class){

     $global = config('admin.middleware.global', []);
     $route  = config('admin.middleware.resources', [])[$model_label] ?? [];

     if($global && $route){
         return array_merge($global, $route);
     }

     if($global && !$route){
         return $global;
     }

     return $route;
 }

 function register_resource_routes($is_platform, $res_ref, $res_class){ 

     $def = $is_platform ? Platform::resources()->get($res_class) : 
     Admin::resources()->get($res_class);

     $authorize = $is_platform ? '__authenticated__ && __super_admin__' : 
     construct_route_authorization($res_ref, $res_class);

     $middleware = $is_platform ? ['__authentication__', '__authorization__'] : 
     construct_route_middleware($res_ref, $res_class);

     Router::context([
         'middleware' => $middleware,
         'authorize'  => $authorize,
         'layout'     => ["saqle.admin.admin", "saqle.admin.resourcewrapper"],
         'model'      => $res_class,
     ])->routes(function() use ($def, $res_ref, $res_class){

         //listing
         Router::get(
             url:    url_join(["/".$res_ref]),
             target: $def->list()->get_component(),
         )->name($res_ref.".list");

         //creating
         Router::route(
             url:    url_join(["/".$res_ref, "create"]),
             target: $def->create()->get_component()
         )->methods(function() use ($res_ref){
             Router::method("GET", "get")->name($res_ref.".create.form");
             Router::method("POST", "post")->name($res_ref.".create");
         });

         //editing
         Router::route(
             url:    url_join(["/".$res_ref, ":id", "edit"]),
             target: $def->edit()->get_component()
         )->methods(function() use ($res_ref){
             Router::method("GET", "get")->name($res_ref.".edit.form");
             Router::method("PATCH", "patch")->name($res_ref.".edit");
         });

         //showing
         Router::get(
             url:    url_join(["/".$res_ref, ":id"]),
             target: $def->show()->get_component(),
         )->name($res_ref.".show");
 
         //deleting
         Router::delete(
             url:    url_join(["/".$res_ref, ":id"]),
             target: $def->delete()->get_component(),
         )->name($res_ref.".delete");

     });
 }