<?php

use SaQle\Routes\Router;

Router::get('/private-file/', 'privatefile');

Router::get('/error/500/', config('app.error_component') ?? 'error500');

?>