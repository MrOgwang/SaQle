<?php

namespace SaQle\Http\Response\Strategies;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\{HttpResponse, HttpMessage};
use SaQle\Http\Response\Types\HtmlResponse;
use SaQle\Core\Components\{ComponentTreeBuilder, ComponentRenderer, ComponentContext};
use SaQle\Templates\Template;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Auth\Models\GuestUser;

final class WebResponseStrategy implements ResponseStrategy {

     public function supports(Request $request): bool {
         return $request->is_web_request();
     }

     private function prepare_context(Request $request){
         $context = [];

         //add feedback context
         //$efb = ExceptionFeedBack::init();
         //$context = array_merge($context, $efb->acquire_context());

         //inject global context data
         $context = array_merge($context, Template::init()::get_context());

         //inject csrf token input here
         $token_key = CsrfMiddleware::get_token_key();
         $token     = CsrfMiddleware::get_token();

         $context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

         //inject the user
         $context['session_user'] = $request->user ?? new GuestUser();

         return $context;
     }

     public function build(Request $request, HttpMessage $result): HttpResponse {
         //use the component tree and component rendere to build the html here

         if($result->code >= 400){
             //construct tree for error page
             //$tree = ErrorComponentTree::from_status($result->status);
         }else{
             $tree = new ComponentTreeBuilder()->build($request->route->compiled_target[0], $request->route->layout);
         }

         $context = new ComponentContext($this->prepare_context($request));

         $renderer = new ComponentRenderer($request);

         $html = $renderer->render($tree, $context);

         $html = $renderer->wrap_root($html);

         return new HtmlResponse($html, $result->code);
     }
}
