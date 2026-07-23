<?php

namespace SaQle\Build\Middleware;

use SaQle\Console\Middleware\BeforeCommandMiddleware;
use SaQle\Auth\Context\ActorContext;

final class SuperUserContextMiddleware implements BeforeCommandMiddleware {
	 public function before($context){
	 	 ActorContext::to_platform();
	 }
}
