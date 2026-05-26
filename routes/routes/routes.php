<?php

use SaQle\Routes\Router;

foreach(config('app.media_storage_drivers') as $storage_key => $storage_config){

	 $route = $storage_config['base_url']."/:storage_key/:file";
	 Router::get($route, config('app.protected_file_component'))->name("app.{$storage_key}.media");
	 
}

Router::get(config('error.route'), config('error.component'))->name('app.error');

Router::get(
	 config('static_assets_route')."/:type/:file", 
	 config('app.static_file_component')
)->name('app.static.asset');

?>