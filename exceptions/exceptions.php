<?php
namespace SaQle\Exceptions;

use SaQle\Orm\Database\Exceptions\ModelNotFoundException;
use SaQle\Orm\Database\Trackers\Exceptions\{DatabaseNotFoundException, TableNotFoundException};
use SaQle\Http\Request\Data\Exceptions\KeyNotFoundException;

/**
* modelnotfoundexception
* @param string $table:            the name of the table
* @param array  $model_references: key => $value array of tables and related model classes
* @param string $database_name   : the name of the database
*/
function modelnotfoundexception(string $table, array $model_references, string $database_name){
	 if(!array_key_exists($table, $model_references)){
	 	 throw new ModelNotFoundException((Object)[
	 	 	'model_name' => $table,
	 	 	'db_context_name' => $database_name
	 	 ]);
	 }
}

function databasenotfoundexception(){

}

function tablenotfoundincontextexception(string $name, array $tables){
	 if(!array_key_exists($name, $data)){
	 	 throw new KeyNotFoundException(name: $name);
	 }
}

function dataitemkeynotfoundexception(string $name, array $data){
	 if(!array_key_exists($name, $data)){
	 	 throw new KeyNotFoundException(name: $name);
	 }
}


?>