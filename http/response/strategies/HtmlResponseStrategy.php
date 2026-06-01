<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message,
     SuccessMessage
};
use SaQle\Http\Response\Types\{
     HtmlResponse
};
use SaQle\Core\Ui\{
     UiComponentTreeBuilder, 
     UiComponentRenderer
};
use SaQle\Core\Ui\Template;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Auth\Models\GuestUser;
use SaQle\Core\Support\AppStage;

final class HtmlResponseStrategy implements ResponseStrategy {
    
     private function prepare_context(Request $request) : array {
         $context = [];

         //inject global context data
         $context = array_merge($context, Template::init()::get_context());

         //inject flash message and context
         $context['flash'] = null;

         $session = request()->session();
         if($session->exists('flash')){
             $context['flash'] = $session->get('flash');
             $session->remove('flash');
         }

         //inject csrf token input here
         $token_key = CsrfMiddleware::get_token_key();
         $token     = CsrfMiddleware::get_token();

         $context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

         $context['session_user'] = $request->user ?? new GuestUser();
         
         return $context;
     }

     private function get_layout(Request $request, Message $result) : array {

         /**
          * The happy path:
          * 
          * 1. We have a route
          * 2. The app stage is AppStage::REQUEST_RESOLUTION
          * 2. The result is a SuccessMessage
          * */
         if(app()->is_stage(AppStage::REQUEST_RESOLUTION) && $result instanceof SuccessMessage && $request->route){
             $target_component = $request->route->compiled_target->name;
             $target_action = $request->route->compiled_target->method ?? null;
             $leaf_component = $target_action ? $target_component."@".$target_action : $target_component;
             $layout = $request->route->layout ?? [];

             return array_merge($layout, [$leaf_component]);
         }

         /**
          * If request hasn't reached resolution stage,
          * it means there was a short circuting at the request middleware
          * stage.
          * 
          * Therefore the result will be treated as a error result
          * no matter what it is.
          * 
          * NOTE: Redirects are handled in RedirectResponseStrategy
          * 
          * */
         if(!app()->is_stage(AppStage::REQUEST_RESOLUTION)){
             return [config('error.component')."@get"];
         }

         /**
          * The request has reached resolution stage.
          * 
          * Assumptions
          * 1. A route exists: unless the developer was crazy and nullified the matched route
          *    in a middleware.
          * 2. The result is a fail result. If it were a success result,
          *    the happy path would have caught it
          * 3. The fail came from executing the controller method.
          * */
         return array_merge($request->route->layout ?? [], [config('error.component')."@get"]);
     }
 
     public function build(Request $request, Message $result) : Response {

         $layout = $this->get_layout($request, $result);
         
         $context = $this->prepare_context($request);

         $tree = new UiComponentTreeBuilder()->build($layout, $result->data ?? []);

         $renderer = new UiComponentRenderer($request);

         $context['__renderer'] = $renderer;

         $html = $renderer->render($tree, $context);

         $html = $renderer->wrap_root($html);
 
         return new HtmlResponse($html);
     }
}