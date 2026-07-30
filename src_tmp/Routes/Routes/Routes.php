<?php

use SaQle\Routes\Router;

/**
 * Media routes
 * */
foreach(config('app.media_storage_drivers') as $storage_key => $storage_config){

	 $app_route = $storage_config['base_url']."/:storage_key/:file";
	 Router::get($app_route, config('protected_file_component'))->name("app.{$storage_key}.media");

	 $platform_root = '/saqle'.$storage_config['base_url']."/:storage_key/:file";
	 Router::get($platform_root, config('protected_file_component'))->name("saqle.{$storage_key}.media");
} 

Router::get(config('error.route'), config('error.component'))->name('app.error');

Router::get(config('static_assets_route')."/:type/:file", config('static_assets_component'))->name('app.static.asset');

Router::get("/saqle".config('static_assets_route')."/:type/:file", config('static_assets_component'))->name('saqle.static.asset');
 
Router::route("/saqle/signin", 'saqle.saqlesignin')->name("saqle.login")->methods(function(){
	 Router::method("GET", "get")->name('form');
	 Router::method("POST", "post")->name('submit');
});

Router::get("/saqle/resources/overview/", 'saqle.dashboard')
->authorize('__authenticated__ && __super_admin__')
->middleware(['__authentication__', '__authorization__'])
->layout(['saqle.app'])
->name('saqle.overview');
 
Router::get("/_admin/resources/overview/", 'saqle.dashboard')
->layout(['saqle.app'])
->name(config('admin.routes.name_prefix', "admin").'.overview');

?>