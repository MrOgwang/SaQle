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

 function register_resource_routes($is_platform, $model_label, $model_class){

     $resource_def = Admin::resources()->get($model_class);
     
     $authorize = $is_platform ? '__authenticated__ && __super_admin__' : 
     construct_route_authorization($model_label, $model_class);

     $middleware = $is_platform ? ['__authentication__', '__authorization__'] : 
     construct_route_middleware($model_label, $model_class);

     //list resources route
     $list_operation = $resource_def?->list();
     Router::get(
     	 url:    admin_route_url($model_label, [], $is_platform),
     	 target: $list_operation ? $list_operation->get_component() : "saqle.lib.resourcelist",
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'list', $is_platform));

     /**
      * Create form and submit create routes.
      * */
     $create_operation = $resource_def?->create();
     $create_component = $create_operation ? $create_operation->get_component() : "saqle.lib.resourcecreate";

     Router::get(
     	 url:    admin_route_url($model_label, ['create'], $is_platform),
     	 target: $create_component,
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'create.form', $is_platform));

     //create resource route
     Router::post(
     	 url:    admin_route_url($model_label, ['create'], $is_platform),
     	 target: $create_component,
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'create', $is_platform));

     /**
      * Edit form and submit edit routes
      * */
     $edit_operation = $resource_def?->edit();
     $edit_component = $edit_operation ? $edit_operation->get_component() : "saqle.lib.resourceedit";

     Router::get(
     	 url:    admin_route_url($model_label, [':id', 'edit'], $is_platform),
     	 target: $edit_component,
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'edit.form', $is_platform));

     //edit resource route
     Router::patch(
     	 url:    admin_route_url($model_label, [':id', 'edit'], $is_platform),
     	 target: $edit_component,
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'edit', $is_platform));

     //show a single resource route
     $show_operation = $resource_def?->show();

     Router::get(
     	 url:    admin_route_url($model_label, [':id'], $is_platform),
     	 target: $show_operation ? $show_operation->get_component() : "saqle.lib.resourceview",
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'view', $is_platform));

     //delete resource route
     $del_operation = $resource_def?->delete();

     Router::delete(
     	 url:    admin_route_url($model_label, [':id'], $is_platform),
     	 target: $del_operation ? $del_operation->get_component() : "saqle.lib.resourcedelete",
     	 model_class:  $model_class
     )
     ->authorize($authorize)
     ->layout(["saqle.admin.admin", "saqle.admin.resourcewrapper"])
     ->middleware($middleware)
     ->name(admin_route_name($model_label, 'delete', $is_platform));
 }