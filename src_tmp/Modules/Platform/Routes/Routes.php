<?php

 declare(strict_types = 1);

 namespace SaQle\Modules\Platform\Routes;

 use SaQle\Orm\Database\SystemSchema;
 use SaQle\Routing\Router;
 
 require_once dirname(__DIR__)."/../Support.php";

 $system_schema = new SystemSchema();

 $system_models = $system_schema->get_defined_models();

 foreach($system_models as $model_label => $model_class){
     register_resource_routes(true, $model_label, $model_class);
 }

 Router::route("/signin", 'saqle.platform.saqlesignin')
 ->middleware(['__guestonly__'])
 ->name("login")
 ->methods(function(){
	 Router::method("GET", "get")->name('form');
	 Router::method("POST", "post")->name('submit');
 });

 Router::post("/signout", 'saqle.platform.saqlesignout')
 ->authorize('__authenticated__ && __super_admin__')
 ->middleware(['__authentication__', '__authorization__'])
 ->name("logout");

 Router::get("/tenants/:slug/manage", 'saqle.platform.managetenant')
 ->authorize('__authenticated__ && __super_admin__')
 ->middleware(['__authentication__', '__authorization__'])
 ->layout(['saqle.admin.admin'])
 ->name('managetenant');

 Router::get("/resources/overview/", 'saqle.admin.dashboard')
 ->authorize('__authenticated__ && __super_admin__')
 ->middleware(['__authentication__', '__authorization__'])
 ->layout(['saqle.admin.admin'])
 ->name('overview');
