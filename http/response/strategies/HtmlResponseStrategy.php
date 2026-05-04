<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{
     Response, 
     Message
};
use SaQle\Http\Response\Types\{
     HtmlResponse
};
use SaQle\Core\Components\{
     ComponentTreeBuilder, 
     ComponentRenderer
};
use SaQle\Core\Ui\Template;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Auth\Models\GuestUser;
use SaQle\Http\Request\Execution\ActionExecutor;

final class HtmlResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->expects_html();
     }

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

     public function build(Request $request, Message $result) : Response {

         $target_component = $request->route->compiled_target->name;
         $target_action = $request->route->compiled_target->method ?? null;
         $leaf_component = $target_action ? $target_component."@".$target_action : $target_component;
         $layout = $request->route->layout ?? [];
         
         $tree = new ComponentTreeBuilder()->build($leaf_component, $layout);

         $renderer = new ComponentRenderer($request);
         $html = $renderer->render(
             $tree, 
             array_merge($this->prepare_context($request), $result->data)
         );

         $html = $renderer->wrap_root($html);

         return new HtmlResponse($html);
     }
}