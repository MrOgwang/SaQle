<?php

namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Factories\TenantIDResolverFactory;

class TenantIDResolver {
     public function resolve() : string|int|null {
         foreach(TenantIDResolverFactory::make() as $resolver){

             $tenant_id = $resolver->resolve();

             if($tenant_id !== null && $tenant_id !== ''){
                 return $tenant_id;
             }
         }

         return null;
     }
}