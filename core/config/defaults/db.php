<?php

/**
 * Database configurations
 * */

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
     'connections' => [],
     
	 /**
	 * When you first setup the db, this class will be used to seed the database with
	 * initial data
	 */
 	 'seeder' => '',
]
?>