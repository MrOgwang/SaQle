<?php

/**
 * These are configurations for multitenancy
 * setup
 * */

use SaQle\Auth\Models\BaseTenant;
use SaQle\Auth\Identity\Tenant\Providers\DefaultTenantProvider;

return [

     /**
      * Whether to enable multitenancy for project or not.
      * 
      * Turn this on/off before any migrations are run
      * */
     'enabled' => true,

     /**
      * Preffered tenant ID source for your project.
      * 
      * Options:
      * 
      * user : The tenant id will be acquired from t he currently logged in user object.
      *        Assumes a tenant_id field on the user model
      * 
      * subdomain : The tenant id is acquired from the request's subdomain
      * 
      * domain : the tenant id is the request's domain
      * 
      * header : the tenant id is a request header
      * 
      * path : the tenant id will be acquired from a url path parameter
      * */
     'id_provider' => 'user',

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
      * The model class representing a tenant.
      * */
     'model_class' => BaseTenant::class,

     'tenant_key' => 'slug',

     'header_name' => 'X-Tenant',

     'path_segment' => 1,

     'cache_session' => true
];

?>