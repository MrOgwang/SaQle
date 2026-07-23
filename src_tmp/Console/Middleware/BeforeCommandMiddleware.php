<?php

namespace SaQle\Console\Middleware;

use SaQle\Middleware\MiddlewareInterface;

interface BeforeCommandMiddleware extends MiddlewareInterface {
	 public function before($context);
}
