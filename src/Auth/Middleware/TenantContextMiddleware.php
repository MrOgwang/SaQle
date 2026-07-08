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
use SaQle\Auth\Identity\Tenant\Interfaces\TenantProviderInterface;
use SaQle\Auth\Identity\Tenant\Resolvers\TenantIDResolver;
use SaQle\Http\Response\Message;
use RuntimeException;

class TenantContextMiddleware implements MiddlewareInterface {

     public function __construct(
         private TenantIDResolver $id_resolver,
         private TenantProviderInterface $tenant_provider
     ){}

     public function handle($request, $response = null) : ?Message {

         $tenant_key = config('session_tenant_key');

         if(
             config('protected_file_component') === $request->route->compiled_target->name || 
             config('static_assets_component') === $request->route->compiled_target->name
         ){
             return null;
         }

         if(auth_context() === 'saqle'){
             $request->session->remove($tenant_key);
             return null;
         }
         
         /**
          * There is always a tenant, even when multi tenancy is turned off. In
          * such a case the tenant id is the name of the app, slugified
          * */
         $tenant_id = config('tenancy.enabled') ? $this->id_resolver->resolve() : slugify(config('app.name'));

         if(!$tenant_id){
             return Message::bad_request(message: "Failed to resolve tenant id!");
         }

         $tenant = $request->session->get($tenant_key, null);
         
         if($tenant && ($tenant->get_id() === $tenant_id || strtolower($tenant->get_name()) === strtolower($tenant_id))){
             return null;
         }

         $tenant = $this->tenant_provider->find($tenant_id);

         if(!$tenant){
             return Message::bad_request(message: "Failed to resolve tenant. Tenant Id - {$tenant_id}!");
         }

         $request->session->set('__tenant', $tenant, true);

         return null;
     }
}