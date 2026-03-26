<?php

use SaQle\Routes\Router;

Router::get('/private-file/', 'privatefile');

Router::get('/error/500/', config('app.error_component') ?? 'error500');

Router::get(config('static_assets_route')."/:type/:file", 'staticfile');

?>