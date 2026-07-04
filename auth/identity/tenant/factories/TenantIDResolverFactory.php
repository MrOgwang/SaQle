<?php
namespace SaQle\Auth\Identity\Tenant\Factories;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;
use SaQle\Auth\Identity\Tenant\Resolvers\{
     SubdomainTenantIDResolver,
     PathTenantIDResolver,
     HeaderTenantIDResolver,
     DomainTenantIDResolver,
     AuthUserTenantIDResolver
};
use RuntimeException;

class TenantIDResolverFactory {

     private const MAP = [
         'user'      => AuthUserTenantIDResolver::class,
         'subdomain' => SubdomainTenantIDResolver::class,
         //'domain'    => DomainTenantIDResolver::class,
         'header'    => HeaderTenantIDResolver::class,
         'path'      => PathTenantIDResolver::class,
     ];

     public static function make() : array {

         $registered_resolvers = config('tenancy.resolvers', []);

         usort($registered_resolvers, function ($a, $b){
             return $a['priority'] <=> $b['priority'];
         });
 
         if(!$registered_resolvers){
             return [new AuthUserTenantIDResolver(key: 'tenant_id')];
         }

         $resolvers = [];

         foreach($registered_resolvers as $r){
             if($r['enabled'] ?? false){

                 $resolver = $r['resolver'];
                 $key = $r['key'] ?? "";

                 if(class_exists($resolver)){
                     $resolvers[] = new $resolver();
                 }else{
                     $resolver_class = self::MAP[$resolver];
                     $resolvers[] = new $resolver_class(key: $key);
                 }
             }
         }

         return $resolvers;
     }
}