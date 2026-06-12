<?php
namespace SaQle\Auth\Identity\Factories;

use SaQle\Auth\Interfaces\TenantIDProviderInterface;
use SaQle\Auth\Identity\Providers\{
     SubdomainTenantIDProvider,
     PathTenantIDProvider,
     HeaderTenantIDProvider,
     DomainTenantIDProvider,
     AuthUserTenantIDProvider
};
use RuntimeException;

class TenantIDProviderFactory {

     public static function make() : TenantIDProviderInterface {

         return match(config('tenancy.id_provider')){
             'subdomain' => new SubdomainTenantIDProvider(),
             'domain'    => new DomainTenantIDProvider(),
             'path'      => new PathTenantIDProvider(),
             'header'    => new HeaderTenantIDProvider(),
             'user'      => new AuthUserTenantIDProvider(),
              default    => throw new RuntimeException('Invalid tenant identity provider')
         };
     }
}