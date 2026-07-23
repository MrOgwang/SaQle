<?php

namespace SaQle\Console\Middleware;

use SaQle\Middleware\MiddlewareInterface;

interface AfterCommandMiddleware extends MiddlewareInterface {
	 public function after($context);
}
