<?php

/**
 * These are configurations for multitenancy
 * setup
 * */

use SaQle\Auth\Models\BaseTenant;
use SaQle\Auth\Identity\Tenant\Providers\DefaultTenantProvider;

return [

     /**
      * -----------------------------------------------------
      * ENABLE/DISABLE MULTITENANCY
      * ---------------------------------------------------------
      * 
      * Turn this on/off before any migrations are run to enable
      * or disanle multi tenancy
      * 
      * */
     'enabled' => false,

     /**
      * ------------------------------------------------
      * TENANT IDENTIFICATION
      * ------------------------------------------------
      * 
      * Resolvers are executed in order. The first resolver that
      * returns a non-null value wins.
      * 
      * path : the tenant id will be acquired from a url path parameter
      * 
      * */
     'resolvers' => [

         /**
          * Get tenant id from the logged in user object
          * */
         [
             'resolver' => 'user',
             'enabled'  => true,
             'key'      => 'tenant_id',
             'priority' => 1
         ],

         /**
          * Get tenant id from request header
          * */
         [
             'resolver' => 'header',
             'enabled'  => true,
             'key'      => 'X-Tenant',
             'priority' => 2
         ],

         /**
          * Get tenant id from url subdomain
          * 
          * Example: tenant.yoursite.com
          * */
         [
             'resolver' => 'subdomain',
             'enabled'  => true,
             'key'      => '',
             'priority' => 4
         ],

         /**
          * Get tenant id from url path
          * 
          * Example: yoursite.com/:tenant/...
          * */
         [
             'resolver' => 'path',
             'enabled'  => true,
             'key'      => 'tenant',
             'priority' => 3
         ]
     ],

     /**
      * --------------------------------------------------
      * TENANT PROVIDER
      * -------------------------------------------------
      * 
      * The tenant provider takes in a Tenant ID and returns
      * an instance of a tenant object to be injected into
      * your request as the session tenant.
      * 
      * This allows you to define how the session tenant is
      * to be represented in your application
      * 
      * */
     'tenant_provider' => DefaultTenantProvider::class,

     /**
      * ------------------------------------------------
      * TENANT MODEL
      * ------------------------------------------------
      * 
      * This model represents a tenant object. Defaults to BaseTenant
      * 
      * */
     'model_class' => BaseTenant::class,

     /**
      * ---------------------------------------------
      * CACHE TENANT
      * ----------------------------------------------
      * 
      * To avoid resolving the tenant on every request,
      * turn this to true in order for the tenant to be cached to session
      * 
      * */
     'cache_session' => true,
];

?>