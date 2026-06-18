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
 * The auth middleware injects the session user into the request
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\MiddlewareInterface;
use SaQle\Auth\Identity\Factories\Tenant\TenantIDResolverFactory;
use SaQle\Auth\Identity\Tenant\Interfaces\TenantProviderInterface;
use SaQle\Http\Response\Message;
use RuntimeException;

class TenantContextMiddleware implements MiddlewareInterface {

     public function handle($request, $response = null) : ?Message {

         $tenant = $request->session->get('__tenant', null);
         
         if($tenant){
             $request->session->set('__tenant', $tenant, true);
             return null;
         }
         
         $id_resolver = TenantIDResolverFactory::make();
         $tenant_id = $id_resolver->resolve();

         if(!$tenant_id){
             return null;
         }

         $tenant_provider = resolve(TenantProviderInterface::class);

         $tenant = $tenant_provider->find($identifier);

         if(!$tenant){
             return null;
         }

         $request->session->set('__tenant', $tenant, true);

         return null;
     }
}