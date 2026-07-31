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

use SaQle\Middleware\RequestMiddleware;
use SaQle\Auth\Identity\Tenant\Interfaces\TenantProviderInterface;
use SaQle\Auth\Identity\Tenant\Resolvers\TenantIDResolver;
use SaQle\Http\Response\Message;
use SaQle\Auth\Context\ActorContext;
use SaQle\Core\Support\Db;
use RuntimeException;

class TenantMiddleware implements RequestMiddleware {

     public function __construct(
         private TenantIDResolver $id_resolver,
         private TenantProviderInterface $tenant_provider
     ){}

     private function register_tenant_databases($tenant){

         //register tenant connections.

         $connections = config('db.connections', []);

         $default_connection_key = config('db.default_connection').".".config('db.default_database');

         $new_default_connection_key = null;

         foreach($connections as $name => $props){
             foreach($props['databases'] as $db => $schema){
                
                 $connection_key = $name.".".$db;

                 [$tenant_connection_key,] = Db::register_tenant_db($connection_key, $tenant);

                 if($default_connection_key === $connection_key){
                     $new_default_connection_key = $tenant_connection_key;
                 }
             }
         }

         //change the default database to a tenant database

         if($new_default_connection_key){

             $connection = explode(".", $new_default_connection_key);

             config()->set('db.default_connection', $connection[0]); 
             config()->set('db.default_database', $connection[1]); 

         }

     }

     public function before($request) : ?Message {

         $tenant_key = config('session_tenant_key');

         if(
             config('protected_file_component') === $request->route->compiled_target->name || 
             config('static_assets_component') === $request->route->compiled_target->name
         ){
             return null;
         }

         if(ActorContext::is_platform()){
             $request->session->remove($tenant_key);
             return null;
         }

         $tenant_id = $this->id_resolver->resolve();

         if(!$tenant_id){
             return Message::bad_request(message: "Failed to resolve tenant id!");
         }

         $tenant = $request->session->get($tenant_key, null);
         
         if($tenant && ($tenant->get_id() === $tenant_id || $tenant->slug === $tenant_id)){

             $this->register_tenant_databases($tenant);

             return null;
         }

         $tenant = $this->tenant_provider->find($tenant_id);

         if(!$tenant){
             return Message::bad_request(message: "Failed to resolve tenant. Tenant Id - {$tenant_id}!");
         }

         $request->session->set($tenant_key, $tenant, true);

         $this->register_tenant_databases($tenant);

         return null;
     }
}