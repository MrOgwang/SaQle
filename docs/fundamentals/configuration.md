# Configuration 

Every application is unique and has unique requirements. Configuration ensures our application works correctly in the environment that it is intended for.

## Environmental Configuration

It is important to have different configuration values depending on where your application is running. For instance, the database connection settings your application uses in development may not be the same as the ones used in production.

Environmental configurations are stored in `env` directory in the project root. In this directory, create subdirectories that reflect each environment where your application will run. Inside each subdirectory is a `.env` file that has your project environmental configuration values.

Consider the following structure: 

```text
App/
├── config/
├── env/
│   ├── development/
│   │   └── .env
│   ├── staging/
│   │   └── .env
│   ├── production/
│   │   └── .env
└── src/
    ├── Bootstrap/
    ├── Components/
    ├── Databases/
    └── Modules/
```

The `SaQle\Core\Support\Environment` has `Environment::DEVELOPMENT`, `Environment::TESTING`, `Environment::STAGING`, `Environment::PRODUCTION` and `Environment::TUNNEL` options.

The `.env.example` file in the project root contains sample environmental configuration keys. 

### Loading Environmental Configuration

In the `src\Bootstrap\App.php` file, set the current environment as shown:

```php
<?php

use SaQle\App\App;
use SaQle\Core\Support\Environment;

return 
App::http(dirname(__DIR__, 2))
->environment(Environment::DEVELOPMENT)
...; /*rest of app bootstrapping here*/ 
```

The correct environment file will then be searched for in the `env` directory and loaded into your application. An exception is thrown if this file is not found.

### Reading Environmental Configuration

You access your environmental configuration using the helper method `env` and passing in a string name, and an optional **default value**.

Consider this **env** file.

```env
DB_HOST=localhost
DB_USER_NAME=root
DB_PASSWORD=
DB_NAME=development
```
You get the configuration values like this:

```php
$db_host = env('db_host', 'localhost');
$db_user_name = env('db_user_name', 'root');
$db_password = env('db_password', '');
$db_name = env('db_name', 'development');
```

### Determining The Current Environment

The `SaQle\Core\Support\Environment` comes with methods that can help you determine the current environment. Use the `current()` method chained with any of `is_development()`, `is_staging()`, `is_tunnel()`, `is_testing()` and `is_production()` methods.

```php
use SaQle\Core\Support\Environment;

if(Environment::current()->is_staging()){
	 //the environment is staging
}
```

## File Configuration

These are settings defined in php files. All file configurations are stored in the `config` directory in the project root.  

### Creating Configuration

### Reading Configuration

### Standard Configurations

SaQle comes with some configuration files already defined.

```text
App/
├── config/
│   ├── Admin.php
│   ├── App.php
│   ├── Auth.php
│   ├── Db.php
│   ├── Dir.php
│   ├── Error.php
│   ├── Mail.php
│   ├── Model.php
│   ├── Queue.php
│   ├── Session.php
│   └── Tenancy.php
├── env/
└── src/
```

#### Admin

#### Application

`App.php` file.

```php
<?php
return [
	 /**
	  * Application name.
	  * */
     'name' => '',

     /**
      * Application's secret cryptographic key
      * This is used to sign component asset urls
      * */
     'key' => '',

     /**
      * whether to display errors
      * */
	 'display_errors' => 0,

     /**
      * whether to display startup errors
      * */
	 'display_startup_errors' => 0,

     //domain configurations
     'domain' => [
         /**
          * The canonical/root domain of the application.
          * Used for URL genetaion and redirects
          * */
         'root' => '',

         /**
          * Domains that represent the central application
          * */
         'hosts' => [],

         /**
          * Hostnames that should never be interpreted
          * as tenants
          * */
         'reserved_subdomains' => ['www']
     ],

 	 /**
      * List of all the modules installed in the project
      * */
 	 'modules' => [],
     
     /**
      * the media url encryption key
      * */
     'media_encrypt_key' => '',

     //the media url encryption salt
     'media_encrypt_salt' => '',

     //media storage drivers
     'media_storage_drivers' => [],

 	 //api url prefixes
 	 'api_url_prefixes' => ['/api/v1/'],

     /**
       * By default, components will be searched in the top level project directory, or inside
       * the individual module directories as listed in modules. 
       * 
       * In cases where your components also exist in other places, list the directory
       * names here relative to the root directory.
     * */
     'extra_components_dirs' => [],

     /**
       * By default, models will be searched in the top level project directory, or inside
       * the individual module directories as listed in modules. 
       * 
       * In cases where your models also exist in other places, list the directory
       * names here relative to the root directory.
     * */
     'extra_models_dirs' => [],

     /**
       * By default, routes will be searched in the top level project directory, or inside
       * the individual module directories as listed in modules. 
       * 
       * In cases where your routes also exist in other places, list the directory
       * names here relative to the root directory.
     * */
     'extra_routes_dirs' => [],

     /**
       * This is the extension of component temnplate files:
       *  
       * Defaults to html
       * */ 
     'component_template_ext' => 'html',

     //system date format
     'system_date_format' => 'd-m-Y',

     //default time zone
 	 'timezone' => 'Africa/Nairobi',
 	 
 	 //system administrator settings
 	 'system_admin_email' => '',
     
 	 'system_admin_name'  => ''
 ]
?>
```

#### Authentication

`Auth.php` file.

```php
<?php
use SaQle\Auth\Models\BaseUser;
use SaQle\Auth\Services\AuthenticationService;
use SaQle\Auth\Identity\User\Providers\DefaultUserProvider;
return [
	 /**
	  * --------------------------------------------
	  * LOG IN STRATEGIES
	  * -------------------------------------------
	  * These are all the ways you would like users 
	  * to log into your app
	  * */
	 'strategies' => [
	 	 'default' => 'password',
	 	 'all'     => ['password', 'google', 'link']
	 ],

     /**
      * --------------------------------------------------
      * USER PROVIDER
      * -------------------------------------------------
      * The user provider takes in a User ID and returns
      * an instance of a user object to be injected into
      * your request as the session user.
      * 
      * This allows you to define how the session user is
      * to be represented in your application
      * */
	 'user_provider' => DefaultUserProvider::class,

     /**
      * -------------------------------------------------
      * LOG IN ROUTE
      * -------------------------------------------------
      * The name of the route responsible for signing in
      * users into your application
      * */
	 'login_route' => null,

	 /**
      * -------------------------------------------------
      * LOG OUT ROUTE
      * -------------------------------------------------
      * The name of the route responsible for signing out
      * users
      * */
	 'logout_route' => null,

	 /**
	  * ------------------------------------------------
	  * MODEL CLASS
	  * ------------------------------------------------
	  * This is the model that represents a user.
	  * 
	  * This class must implement a UserInterface
	  * */
	 'model_class' => BaseUser::class,

     /**
      * ------------------------------------------------------
      * BACKEND CLASS
      * ------------------------------------------------------
      * This is the service class responsible for user authentication
      * */
 	 'backend_class' => AuthenticationService::class,
 	 
 	 /**
 	  * ------------------------------------------------------------
 	  * PASSWORDS
 	  * -----------------------------------------------------------
 	  * Preferred password hashing strategies for your app
 	  * */
 	 'passwords' => [
 	 	 'default' => [
 	 	 	 'algorithm' => PASSWORD_ARGON2ID,
		     'options' => [
		         'memory_cost' => 65536,
		         'time_cost' => 4,
		         'threads' => 2,
		     ],
 	 	 ]
 	 ],

     /**
      * the jwt token key
      * */
 	 'jwt_key' => '',

 	 /**
 	  * Number of minutes a jwt token remains valid.
 	  * Defaults to 5 minutes
 	  * */
 	 'jwt_ttl' => 5,

 	 /**
 	  * the jwt issuer
 	  * */
 	 'jwt_iss' => ''
 ]
?>
```

#### Database

`Db.php` file.

```php
<?php
return [
	 /**
      * The default connection to use.
      * 
      * This only takes effect where more than one connection is listed.
      * 
      * If not provided, the first connection in the list
      * of connections will always be used as the default connection.
      * 
      * */
     'default_connection' => '',

      /**
      * The default database to use.
      * 
      * This only takes effect where more than one database is listed for the default connection.
      * 
      * If not provided, the first database in the list
      * of databases for the default connection will be 
      * used as the default database.
      * 
      * */
     'default_database' => '',

     /**
      * Define all the database connections
      * */
     'connections' => []
]
?>
```

#### Directory

#### Error

#### Mail

#### Model

#### Queue

#### Session

`Session.php` file.

```php
<?php
use SaQle\Session\SessionHandler;
return [
      /**
      * -----------------------------------------------
      * COOKIE DOMAIN
      * -----------------------------------------------
      * Which domain the session cookie is valid for
      * Example: 
      * - If set to .example.com, the cookie works for www.example.com, 
      * api.example.com, app.example.com.
      * - If left blank, it defaults to the current host only
      * 
      * This is important if you want a single signon 
      * across multiple sub domains
      * */
      'cookie_domain' => env('cookie_domain', $_SERVER['HTTP_HOST'] ?? ''),

      /**
      *-------------------------------------------------------
      * SESSION HANDLER
      * -----------------------------------------------------
      * The mechanism by which session data is stored and retrieved
      * 
      * By default PHP saves sessions in files on disk (/tmp/sess_xxx)
      * But you can override this with your own storage 
      * (e.g., database, Redis, Memcached).
      * */
      'handler' => SessionHandler::class,

      /**
      * ---------------------------------------------------------
      * SESSION GC MAXLIFETIME
      * ----------------------------------------------------------
      * Lifetime (in seconds) of session data stored on the server 
      * before garbage collection may delete it.
      * 
      * Controls how long session data exists on the server. 
      * If too low, users get logged out often
      * 
      * Defaults to 1440 seconds(24 minutes)
      * */
     'gc_maxlifetime' => env('session_gc_maxlifetime', 1440),

      /**
       * ------------------------------------------------------
       * SESSION COOKIE LIFETIME
       * -----------------------------------------------------
       * Lifetime (in seconds) of the cookie on the client's browser.
       * 
       * Controls how long the browser remembers the session ID.
       * 
       * Defaults to 0 - which means until browser is closed
       * */
     'cookie_lifetime' => env('session_cookie_lifetime', 0),

     /**
      * ----------------------------------------------------
      * SESSION GC PROBABILITY & SESSION GC DIVISOR
      * ---------------------------------------------------
      * Controls how often the garbage collection is run
      * 
      * Example:
      * probability / divisor = chance that this request will trigger GC.
      * gc_probability = 1, gc_divisor = 100 → 1% chance per request.
      * gc_probability = 1, gc_divisor = 1 → always run GC.
      * 
      * Garbage collection is expensive, 
      * so PHP doesn't run it on every request. 
      * 
      * Tuning this helps balance between stale session 
      * files vs. performance.
      * */
     'gc_probability' => env('session_gc_probability', 1),
     'gc_divisor' => env('session_gc_divisor', 100),
 ]
?>
```

#### Tenancy

`Tenancy.php` file.

```php
<?php
use SaQle\Auth\Models\BaseTenant;
use SaQle\Auth\Identity\Tenant\Providers\DefaultTenantProvider;
return [
     /**
      * -----------------------------------------------------
      * ENABLE/DISABLE MULTITENANCY
      * ---------------------------------------------------------
      * Turn this on/off before any migrations are run to enable
      * or disable multi tenancy
      * */
     'enabled' => false,

     /**
      * ------------------------------------------------
      * TENANT IDENTIFICATION
      * ------------------------------------------------
      * Resolvers are executed in order. The first resolver that
      * returns a non-null value wins.
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
          * Example: yoursite.com/:tenant/...
          * */
         [
             'resolver' => 'path',
             'enabled'  => true,
             'key'      => 'tenant',
             'priority' => 3
         ],
         /**
          * Get tenant id from session
          * */
         [
             'resolver' => 'session',
             'enabled'  => true,
             'key'      => 'tenant_id',
             'priority' => 5
         ]
     ],

     /**
      * --------------------------------------------------
      * TENANT PROVIDER
      * -------------------------------------------------
      * The tenant provider takes in a Tenant ID and returns
      * an instance of a tenant object to be injected into
      * your request as the session tenant.
      * 
      * This allows you to define how the session tenant is
      * to be represented in your application
      * */
     'tenant_provider' => DefaultTenantProvider::class,

     /**
      * ------------------------------------------------
      * TENANT MODEL
      * ------------------------------------------------
      * This model represents a tenant object. Defaults to BaseTenant
      * */
     'model_class' => BaseTenant::class,

     /**
      * ---------------------------------------------
      * CACHE TENANT
      * ----------------------------------------------
      * To avoid resolving the tenant on every request,
      * turn this to true in order for the tenant to be cached to session
      * */
     'cache_session' => true,
];

?>
```

### Runtime Overrides

dtqw87dtqwdqw8dt qwdt8qwd qwdfqwd wqd

