<?php

use SaQle\Routes\Router;

foreach(config('app.media_storage_drivers') as $storage_key => $storage_config){

	 $route = $storage_config['base_url']."/:storage_key/:file";
	 Router::get($route, 'protectedfile')->name("app.{$storage_key}.media");
	 
}

Router::get(config('error.route'), config('error.component'))->name('app.error');

Router::get(config('static_assets_route')."/:type/:file", 'staticfile')->name('app.static.asset');

?>