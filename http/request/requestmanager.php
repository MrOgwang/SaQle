<?php
namespace SaQle\Http\Request;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Session\Middleware\SessionMiddleware;
use SaQle\Routes\Middleware\RoutingMiddleware;
use SaQle\Http\Request\Middleware\DataConsolidatorMiddleware;
use SaQle\Auth\Middleware\AuthMiddleware;
use SaQle\Permissions\Middleware\PermissionsMiddleware;
use SaQle\Views\{TemplateView, TemplateOptions};
use SaQle\Controllers\Attributes\{DbCtxClasses};
use SaQle\Controllers\Middleware\ControllerTrackerMiddleware;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

class RequestManager{
	 public function __construct(private Request $request){

     }

     private function assign_middlewares(MiddlewareInterface $middleware, array $middlewares, int $index = 0){

         if($index < count($middlewares)){
             $next_middleware          = $middlewares[$index];
             $next_middleware_instance = new $next_middleware();
             $middleware->next($next_middleware_instance);
             $this->assign_middlewares($next_middleware_instance, $middlewares, $index + 1);
         }

     }

     private function get_controller_db_contexts(string $controller_class){
         $reflector   = new \ReflectionClass($controller_class);
         $attributes  = $reflector->getAttributes(DbCtxClasses::class);
         $contexts    = [];
         if($attributes){
             $instance = $attributes[0]->newInstance();
             $classes = $instance->get_classes();
             foreach($classes as $cls){
                 $contexts[] = Cf::create(ContainerService::class)->createDbContext($cls);
             }
         }
         return $contexts;
     }

     public function process(){
         date_default_timezone_set(DEFAULT_TIMEZONE);
         $request_middlewares = [
             SessionMiddleware::class,
             AuthMiddleware::class,
             RoutingMiddleware::class,
             DataConsolidatorMiddleware::class,
             PermissionsMiddleware::class,
             ControllerTrackerMiddleware::class
         ];
         $middleware          = $request_middlewares[0];
         $middleware_instance = new $middleware();
         $this->assign_middlewares($middleware_instance, $request_middlewares, 1);
         $middleware_instance->handle($this->request);

         $target           = $this->request->final_route->get_target();
         $target_parts     = explode("@", $target[0]);
         $target_classname = $target_parts[0];
         if(str_contains($target_classname, 'Controllers')){
            $target_instance = new $target_classname(request: $this->request, context: $this->get_controller_db_contexts($target_classname));
            if($this->request->final_route->is_api_request()){
                 $target_instance->api_instance()->respond();
            }else{
                 $target_instance->web_instance()->view();
            }
         }else{
             $template_path    = $target[1] ?? null;
             $template_name    = $this->request->final_route->get_actual_template_name($template_path);
             $template_path    = $this->request->final_route->get_actual_template_path($template_path);
             $template_context = $target[2] ?? [];
             $view = new TemplateView($this->request, new TemplateOptions($template_name, $template_path, $template_context, false));
             $view->render();
         }
     }
}
?>