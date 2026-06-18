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

     public static function make() : TenantIDResolverInterface {

         return match(config('tenancy.id_provider')){
             'subdomain' => new SubdomainTenantIDResolver(),
             'domain'    => new DomainTenantIDResolver(),
             'path'      => new PathTenantIDResolver(),
             'header'    => new HeaderTenantIDResolver(),
             'user'      => new AuthUserTenantIDResolver(),
              default    => throw new RuntimeException('Invalid tenant identity provider')
         };
     }
}