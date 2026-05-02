<?php

/**
 * Error configurations
 * */

return [
     /**
      * This is the component or the controller class name
      * that is targetted by the error route
      * */
     'component' => 'error',

     /**
      * This is the route that will display an error page
      * when an exception occurs
      * */
     'route' => '/error/:code/',

     /**
      * Whether to log exceptions to a file or not. 
      * 
      * If true, all exceptions are logged to a file as defined
      * by error_log_file
      * */
     'should_log' => true,

     /**
      * The file to log errors to. 
      * 
      * */
     'log_file' => 'errors'
];

?>