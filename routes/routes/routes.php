<?php

use SaQle\Routes\Router;

Router::get('/private-file/', 'privatefile');

Router::get(config('error.route'), config('error.component'))->name('app.error');

Router::get(config('static_assets_route')."/:type/:file", 'staticfile');

?>