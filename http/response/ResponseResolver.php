<?php

namespace SaQle\Http\Response;

use SaQle\Http\Request\Request;
use SaQle\Http\Response\Strategies\{
     SseResponseStrategy, 
     JsonResponseStrategy, 
     HtmlResponseStrategy,
     RedirectResponseStrategy,
     FileResponseStrategy
};
use RuntimeException;

final class ResponseResolver {

     public function resolve(Request $request, ?HttpMessage $result = null) : Response {

         $response_strategies = [
             new SseResponseStrategy(),
             new JsonResponseStrategy(),
             new FileResponseStrategy()
         ];

         /**
          * The only time the result is not null, is when an exception has happened
          * 
          * An exception occured and this is a post/put/patch/delete request
          * */
         if($result && strtolower($request->route->method) !== 'get'){

             $response_strategies[] = new RedirectResponseStrategy();
 
             /**
              * When exceptions happen for submit requests(post, put, patch, delete), this is the redirect
              * philosophy:
              * 
              * ValidationException(validation related errors) - redirect back
              * DomainException(business rules related errors) - (redirect to a meaninful page defined by the developer)
              * SystemErrors                                   - redirect to the relevant error page
              * */

         }else{
             $response_strategies[] = new HtmlResponseStrategy();
         }

         foreach($response_strategies as $strategy){
             if($strategy->supports($request)){
                 return $strategy->build($request, $result);
             }
         }

         throw new RuntimeException('No response strategy matched');
     }
}
