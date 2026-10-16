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
 * The tenant middleware injects the tenant into the request
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Auth\Middleware;

use SaQle\Middleware\{   
     RequestMiddleware,
     ResponseMiddleware
};
use SaQle\Auth\Identity\Tenant\Interfaces\TenantProviderInterface;
use SaQle\Auth\Identity\Tenant\Resolvers\TenantIDResolver;
use SaQle\Http\Message\Message;
use SaQle\Auth\Context\ActorContext;
use SaQle\Core\Support\{
     Db,
     Session
};
use RuntimeException;

class TenantMiddleware implements RequestMiddleware, ResponseMiddleware {

     public function __construct(
         private TenantIDResolver $id_resolver,
         private TenantProviderInterface $tenant_provider
     ){}

     public function before($request) : Message {

         $tenant_key = config('session_tenant_key');

         if(
             str_contains($request->route->target, config('protected_file_component')) ||
             str_contains($request->route->target, config('static_assets_component'))
         ){
             return Message::ok();
         }

         if(ActorContext::is_platform()){
             $request->session()->remove($tenant_key);
             return Message::ok();
         }

         $tenant_id = $this->id_resolver->resolve();

         if(!$tenant_id){
             return Message::ok();
         }

         $tenant = $request->session()->get($tenant_key, null);
         
         if($tenant && ($tenant->get_id() === $tenant_id || $tenant->slug === $tenant_id)){
             return Message::ok();
         }

         $tenant = $this->tenant_provider->find($tenant_id);

         if(!$tenant){
             return Message::bad_request(message: "Failed to resolve tenant. Tenant Id - {$tenant_id}!");
         }

         $request->session()->set($tenant_key, $tenant);

         if(Session::has('__manage_tenant__')){

             $auth_model = config('auth.model_class');

             $user = $auth_model::get()->where('user_id', $request->user->user_id)->first_or_null();

             if(!$user){
                 $request->user->save();
             }

         }

         return Message::ok();
     }

     public function after($request, $response) : Message {
         if(Session::has('__manage_tenant__')){
             Session::remove('__manage_tenant__');
         }

         return Message::ok();
     }
}