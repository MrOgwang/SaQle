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

use SaQle\Middleware\IMiddleware;
use SaQle\Http\Request\Request;
use SaQle\Http\Response\Response;
use SaQle\Routes\Canonical\CanonicalUrlPolicy;

final class CanonicalUrlMiddleware extends IMiddleware {

     private CanonicalUrlPolicy $policy;

     public function __construct(){
         $this->policy = resolve(CanonicalUrlPolicy::class);
     }

     public function handle(Request $request, ?Response $response = null){
         //only safe methods
         if(!in_array($request->method(), ['GET', 'HEAD'])) {
             parent::handle($request, $response);
             return;
         }

         $redirect = $this->policy->canonicalize($request);

         if($redirect){
             redirect($redirect->location, $redirect->status);
         }

         parent::handle($request, $response);
     }
}
