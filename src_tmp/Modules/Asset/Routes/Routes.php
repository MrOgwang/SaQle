<?php

declare(strict_types = 1);

namespace SaQle\Modules\Asset\Routes;

use SaQle\Routing\Router;

/**
 * Media routes
 * */
foreach(config('app.media_storage_drivers') as $storage_key => $storage_config){

	 $app_route = $storage_config['base_url']."/:storage_key/:file";
	 Router::get($app_route, config('protected_file_component'))->name("app.{$storage_key}.media");

	 $platform_root = '/saqle'.$storage_config['base_url']."/:storage_key/:file";
	 Router::get($platform_root, config('protected_file_component'))->name("saqle.{$storage_key}.media"); 
}  

Router::get(config('static_assets_route')."/:type/:file", config('static_assets_component'))->name('app.static.asset');
  
Router::get("/saqle".config('static_assets_route')."/:type/:file", config('static_assets_component'))->name('saqle.static.asset');

?>