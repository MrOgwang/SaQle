<?php
namespace SaQle\Auth\Identity\Tenant\Resolvers;

use SaQle\Auth\Identity\Tenant\Interfaces\TenantIDResolverInterface;

class SubdomainTenantIDResolver implements TenantIDResolverInterface {

	 public function __construct(private string $key){}

	 public function resolve() : null|int|string {

	 	 $host = strtolower(request()->host());

	 	 $base_domains = config('app.domain.hosts', []);

	 	 foreach($base_domains as $domain){
             
             $domain = strtolower($domain);

             if($host === $domain){
                 return null;
             }

             //host is not under this central domain.
             if(!str_ends_with($host, '.'.$domain)){
                 continue;
             }

             //everything before the central domain is the subdomain portion.
             $subdomain = substr($host, 0, -strlen('.'.$domain));

             //we only support a single tenant subdomain.
             if(str_contains($subdomain, '.')){
                 return null;
             }

             if(in_array($subdomain, config('app.domain.reserved_subdomains', []), true)){
                 return null;
             }

             return $subdomain;
         }

         return null;
	 }
}