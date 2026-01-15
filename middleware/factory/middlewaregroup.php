<?php
namespace SaQle\Middleware\Factory;

use SaQle\Middleware\Interface\ScopedMiddleware;
use SaQle\Middleware\Base\BaseMiddlewareGroup;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Auth\Middleware\AuthenticationMiddleware;
use SaQle\Routes\Middleware\{CanonicalUrlMiddleware, RoutingMiddleware};
use SaQle\Http\Request\Middleware\{DataMiddleware, CsrfMiddleware, RequestIntentMiddleware};
use SaQle\Auth\Middleware\AuthorizationMiddleware;
use SaQle\Http\Cors\Middlewares\CorsMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use SaQle\Http\Request\Request;

class MiddlewareGroup extends BaseMiddlewareGroup{
	 protected function get_pre_routing_middlewares(): array {
         return [
         	 RequestIntentMiddleware::class,
             RoutingMiddleware::class,
         ];
     }

     protected function get_post_routing_middlewares(): array {
         return [
         	 CanonicalUrlMiddleware::class,
             CorsMiddleware::class,
             SessionMiddleware::class,
             AuthenticationMiddleware::class,
             DataMiddleware::class,
             CsrfMiddleware::class,
             AuthorizationMiddleware::class,
             ...app()->middleware->all(),
         ];
     }

     protected function filter_by_intent(array $middlewares, MiddlewareRequestInterface $request): array {
         return array_values(array_filter($middlewares, function ($middleware) use ($request) {
             if (!is_subclass_of($middleware, ScopedMiddleware::class)) {
                 return true; // default allow
             }

             return in_array(
                 $request->intent,
                 $middleware::scopes(),
                 true
             );
         }));
     }

     public function handle(MiddlewareRequestInterface &$request): Request {
         /** PHASE 1 — Routing (intent resolution happens here) */
         $pre = $this->get_pre_routing_middlewares();
         $this->run_chain($pre, $request);

         /** PHASE 2 — Intent-aware middlewares */
         $post = $this->get_post_routing_middlewares();
         $post = $this->filter_by_intent($post, $request);
         $this->run_chain($post, $request);

         return $request;
     }

     protected function run_chain(array $middlewares, MiddlewareRequestInterface &$request): void {
         if (!$middlewares) {
             return;
         }

         $middleware = $middlewares[0];
         $instance   = new $middleware();

         $this->assign_middlewares($instance, $middlewares, 1);
         $instance->handle($request);
     }
}
