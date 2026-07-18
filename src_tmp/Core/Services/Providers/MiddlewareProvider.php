<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Routes\Middleware\{
     CanonicalUrlMiddleware
};
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Auth\Middleware\{
      AuthorizationMiddleware,
      TenantContextMiddleware,
      AuthenticationMiddleware
};
use SaQle\Http\Cors\Middlewares\CorsMiddleware;
use SaQle\Http\Request\RequestScope;

class MiddlewareProvider extends ServiceProvider {
     public function register(): void {   

         //register middleware
         $this->app->middleware->add('authentication', AuthenticationMiddleware::class);
         $this->app->middleware->add('canonicalurl', CanonicalUrlMiddleware::class, RequestScope::WEB);
         $this->app->middleware->add('cors', CorsMiddleware::class);
         $this->app->middleware->add('csrf', CsrfMiddleware::class, RequestScope::WEB);
         $this->app->middleware->add('authorization', AuthorizationMiddleware::class);
         $this->app->middleware->add('tenantcontext', TenantContextMiddleware::class);

     }
}

