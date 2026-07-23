<?php

return [

     //the name of the application.
     'name' => '',

     //whether to display errors
	 'display_errors' => 0,

     //whether to display startup errors
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
      * List of all the modules in the project. 
      * A module is generally a folder with controllers, templates and routes
      * */
 	 'modules' => [],
     
     //the media url encryption key
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