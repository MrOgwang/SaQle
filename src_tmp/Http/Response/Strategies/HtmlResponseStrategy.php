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
use SaQle\Core\Components\{
     Tree,
     Renderer
};
use SaQle\Auth\Models\GuestUser;
use SaQle\Core\Support\AppStage;
use SaQle\Core\Ui\PageManager;

final class HtmlResponseStrategy implements ResponseStrategy {
    
     private function prepare_context(Request $request) : array {
         $context = [];

         //inject flash message and context
         $context['flash'] = null;

         $session = request()->session();
         if($session->exists('flash')){
             $context['flash'] = $session->get('flash');
             $session->remove('flash');
         }

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
             $layout = $request->route->layout ?? [];
             return array_merge([config('page_component')], $layout, [$request->route->target]);
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
             return [config('page_component'), config('error.component')."@get"];
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
         return array_merge(
             [config('page_component')], 
             $request->route->layout ?? [], 
             [config('error.component')."@get"]
         );
     }

     private function inject_page_header(string $html): string {

         $assets = PageManager::init()->output();

         return preg_replace_callback(
             '/<ui:(scripts|styles|meta|title)\s*(?:\/>|>(.*?)<\/ui:\1\s*>)/is',
             function (array $matches) use ($assets): string {

                 $type = $matches[1];

                 $default = isset($matches[2]) ? trim($matches[2]) : '';

                 $generated = match($type){
                     'scripts' => $assets['js'] ?? '',
                     'styles'  => $assets['css'] ?? '',
                     'meta'    => $assets['meta'] ?? '',
                     'title'   => $assets['title'] ?? '',
                     default   => '',
                 };

                 if($type === 'title'){
                     //generated title takes precedence over default title.
                     return $generated !== '' ? $generated : $default;
                 }

                 //meta, styles and scripts: preserve defaults and append generated content.
                 return $default."\n".$generated;
             },

             $html
         );
     }
 
     public function build(Request $request, Message $result) : Response {

         $layout = $this->get_layout($request, $result);

         $context = $this->prepare_context($request);

         $tree = new Tree()->build($request, $layout);

         $renderer = new Renderer($request);

         $html = $renderer->render($tree, $context);

         $html = $this->inject_page_header($html);
 
         return new HtmlResponse($html);
     }
}