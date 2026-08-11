<?php

declare(strict_types = 1);

namespace SaQle\Lib\Routes;

use SaQle\Routing\Router;

Router::get(config('error.route'), config('error.component'))->name('app.error');

?>