<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * This middleware ensures canonical url formats
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Routes\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Routes\Canonical\CanonicalUrlPolicy;
use SaQle\Http\Response\HttpMessage;

final class CanonicalUrlMiddleware implements MiddlewareInterface {

     private CanonicalUrlPolicy $policy;

     public function __construct(){
         $this->policy = resolve(CanonicalUrlPolicy::class);
     }

     public function handle($request, $response = null) : ?HttpMessage {

         if($request->is_unsafe()){
             return null;
         }

         $redirect = $this->policy->canonicalize($request);
         if(!$redirect){
             return null;
         }

         return redirect($redirect->location, $redirect->status);
     }
}
