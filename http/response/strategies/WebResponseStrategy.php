<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};
use SaQle\Http\Response\Types\HtmlResponse;
use SaQle\Core\Components\{ComponentTreeBuilder, ComponentRenderer, ComponentContext};
use SaQle\Core\Ui\Template;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Auth\Models\GuestUser;
use SaQle\Http\Request\Execution\ActionExecutor;

final class WebResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->is_web_request();
     }

     private function prepare_context(Request $request){
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

         //inject the user
         $context['session_user'] = $request->user ?? new GuestUser();
         
         return $context;
     }

     public function build(Request $request, ?HttpMessage $result = null) : HttpResponse {

         $target_component = $request->route->compiled_target->name;

         //use the component tree and component rendere to build the html here
         if(in_array($target_component, ['privatefile', 'staticfile'])){
             $result = ActionExecutor::execute($request);
             return new HtmlResponse('', $result->code);
         }

         $target_action = $request->route->compiled_target->method ?? null;
         $leaf_component = $target_action ? $target_component."@".$target_action : $target_component;
         $layout = $request->route->layout ?? [];
         $context = $this->prepare_context($request);

         if($result){
             $leaf_component = config('error.component')."@get";
             $layout = [];
             $context = array_merge($context, [
                 'message' => $result->message, 
                 'code' => $result->code
             ]);
         }

         $tree = new ComponentTreeBuilder()->build($leaf_component, $layout);

         $context = new ComponentContext($context);

         $renderer = new ComponentRenderer($request);

         $html = $renderer->render($tree, $context);

         $html = $renderer->wrap_root($html);

         return new HtmlResponse($html);
     }
}