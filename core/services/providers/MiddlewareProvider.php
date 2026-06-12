<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Routes\Middleware\{
     CanonicalUrlMiddleware
};
use SaQle\Http\Request\Middleware\{
     DataMiddleware, 
     CsrfMiddleware
};
use SaQle\Auth\Middleware\{
      AuthorizationMiddleware,
      TenantContextMiddleware
};
use SaQle\Http\Cors\Middlewares\{
     CorsMiddleware,
     ApplyCorsHeadersMiddleware
};
use SaQle\Http\Request\RequestScope;

class MiddlewareProvider extends ServiceProvider {
     public function register(): void {

         //register middleware
         $this->app->middleware->add('canonicalurl', CanonicalUrlMiddleware::class, RequestScope::WEB);
         $this->app->middleware->add('cors', CorsMiddleware::class);
         $this->app->middleware->add('data', DataMiddleware::class);
         $this->app->middleware->add('csrf', CsrfMiddleware::class, RequestScope::WEB);
         $this->app->middleware->add('authorization', AuthorizationMiddleware::class);
         $this->app->middleware->add('applycors', ApplyCorsHeadersMiddleware::class);
         $this->app->middleware->add('tenantcontext', TenantContextMiddleware::class);

         //assign request middlware: middleware is executed top to bottom
         $request_middlewares = [
             'canonicalurl',
             'cors',
             'data',
             'csrf',
             'authorization'
         ];

         if(config('tenancy.enabled')){
             $request_middlewares[] = 'tenantcontext';
         }

         $this->app->middleware->request($request_middlewares);

         //assign response middlware: middleware is executed top to bottom
         $this->app->middleware->response([
             'applycors'
         ]);

     }
}

