<?php

 use SaQle\Admin\Admin;
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

     $def = Admin::resources()->get($res_class);

     echo "Ref: $res_ref, Class: $res_class\n";
     print_r(Admin::resources());
     echo "\n---------------------\n";

     $authorize = $is_platform ? '__authenticated__ && __super_admin__' : 
     construct_route_authorization($res_ref, $res_class);

     $middleware = $is_platform ? ['__authentication__', '__authorization__'] : 
     construct_route_middleware($res_ref, $res_class);

     /*Router::context([
         'middleware' => $middleware,
         'authorize'  => $authorize,
         'layout'     => ["saqle.admin.admin", "saqle.admin.resourcewrapper"],
         'prefix'     => $is_platform ? "/saqle" : config('admin.routes.prefix', "/_admin"),
         'model'      => $res_class,
         'name'       => $is_platform ? "saqle" : config('admin.routes.name_prefix', "admin"),
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
         )->methods(function(){
             Router::method("GET", "get")->name($res_ref.".create.form");
             Router::method("POST", "post")->name($res_ref.".create");
         });

         //editing
         Router::route(
             url:    url_join(["/".$res_ref, ":id", "edit"]),
             target: $def->edit()->get_component()
         )->methods(function(){
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

     });*/
 }