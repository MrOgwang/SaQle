<?php

use SaQle\Core\Files\Storage\Drivers\LocalStorageDriver;

return [

     //the name of the application.
     'name' => '',

     //whether to display errors
	 'display_errors' => 0,

     //whether to display startup errors
	 'display_startup_errors' => 0,

     //the root domain
 	 'root_domain' => "",

 	 /**
      * List of all the modules in the project. 
      * A module is generally a folder with controllers, templates and routes
      * */
 	 'modules' => [],

 	 //the name of the root media folder
 	 'media_folder' => 'media',

     //the url for media
     'media_url' => '/media/',

     //the media url encryption key
     'media_key' => '',

     //the url prefix for cron jobs
     'cron_url' => '/cron/',

 	 //whether to keep media in document root i.e public folder
 	 'hidden_media_folder' => false,

     //media storage drivers
     'media_storage_drivers' => [
         'local' => [
             'driver' => LocalStorageDriver::class,
             'root' => '/var/www/app/public/uploads',
             'visibility' => 'public',
             'base_url' => 'https://example.com/uploads',
         ],
     ],

 	 //api url prefixes
 	 'api_url_prefixes' => ['/api/v1/'],

 	 //sse url prefixes
 	 'sse_url_prefixes' => ['/sse/v1/'],

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
       * This is the extension of component temnplate files:
       * 
       * Defaults to html
       * */
     'component_template_ext' => 'html',

     //date and time formats
 	 'date_added_format' => 'jS M Y',
 	 'date_display_format' => 'jS M Y',
 	 'datetime_display_format' => 'jS M Y h:s:m a',
     'system_date_format' => 'd-m-Y',

     //default time zone
 	 'timezone' => 'Africa/Nairobi',
 	 
 	 //system administrator settings
 	 'system_admin_email' => '',
 	 'system_admin_name'  => ''
 ]
?>