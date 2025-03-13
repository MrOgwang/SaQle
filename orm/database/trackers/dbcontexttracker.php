<?php
declare(strict_types = 1);

namespace SaQle\Orm\Database\Trackers;

use SaQle\Orm\Database\Trackers\Exceptions\TableNotFoundException;
use SaQle\Orm\Database\Trackers\Exceptions\DatabaseNotFoundException;

class DbContextTracker{

     //a list of all the table names that will be rerenced in this query.
	 public array $tables = [] {
	 	 set(array $value){
	 	 	$this->tables = $value;
	 	 }

	 	 get => $this->tables;
	 }

     //a list of all the table name aliases that will be refenced in this query.
	 public array $aliases = [] {
	 	 set(array $value){
	 	 	$this->aliases = $value;
	 	 }

	 	 get => $this->aliases;
	 }

     //in saql statatements, this will be prefered over the table name
	 public array $tablerefs = [] {
	 	 set(array $value){
	 	 	$this->tablerefs = $value;
	 	 }

	 	 get => $this->tablerefs;
	 }

     //a list of all the databases that will be refenced in this query.
	 public array $databases = [] {
	 	 set(array $value){
	 	 	$this->databases = $value;
	 	 }

	 	 get => $this->databases;
	 }

     //an array of all tables and respective fields as referenced in this query.
	 public array $fieldrefs = [] {
	 	 set(array $value){
	 	 	$this->fieldrefs = $value;
	 	 }

	 	 get => $this->fieldrefs;
	 }

     //file field settings
	 public array $ffsettings = [] {
	 	 set(array $value){
	 	 	$this->ffsettings = $value;
	 	 }

	 	 get => $this->ffsettings;
	 }

	 /*
	     - given a table name and a field name, find the index of that table in the tracker
	     @param string field_name, 
	         - given in the format 'users:first_name' or just 'first_name'
	         - if given in the first format, the table_name may change because this indicates a join to another table.
	     @param string table_name
	 */
	 public function find_table_index(string $table_name, string $field_name){
	 	 $field_name_array = explode(":", $field_name);
	 	 $table_name_changed = false;
		 if(count($field_name_array) > 1){
		 	 $table_name_changed = true;
		 	 $table_name = $field_name_array[0];
		 }
		 return ['table_index' => array_search($table_name, $this->tables, true), 'name_changed' => $table_name_changed];
	 }

	 /*
	    Find the name of a table given its index
	    @param int $table_index
	    @return string $table_name:
	    @throw TableNotFoundException
	 */
	 public function find_table_name(int $table_index) : string{
	 	 if($table_index < 0 || $table_index >= count($this->tables)){
	 	 	 throw new TableNotFoundException((Object)[
	 	 	     'table_name'  => "",
	 	 	     'table_index' => $table_index,
	 	 	     'tables'      => $this->tables
	 	     ]);
	 	 }
	 	 return $this->tables[$table_index];
	 }
	 /*
	    Find the aliase of a table given its index
	    @param int $table_index
	    @return string $table_aliase:
	    @throw TableNotFoundException
	 */
	 public function find_table_aliase(int $table_index) : string{
	 	 if($table_index < 0 || $table_index >= count($this->tables)){
	 	 	 throw new TableNotFoundException((Object)[
	 	 	     'table_name'  => "",
	 	 	     'table_index' => $table_index,
	 	 	     'tables'      => $this->tables
	 	     ]);
	 	 }
	 	 return $this->aliases[$table_index];
	 }
	 /*
	    Find the table refrence given its index
	    @param int $table_index
	    @return string $table_refernce:
	    @throw TableNotFoundException
	 */
	 public function find_table_refernce(int $table_index) : ?string{
	 	 if($table_index < 0 || $table_index >= count($this->tablerefs)){
	 	 	 throw new TableNotFoundException((Object)[
	 	 	     'table_name'  => "",
	 	 	     'table_index' => $table_index,
	 	 	     'tables'      => $this->tablerefs
	 	     ]);
	 	 }
	 	 return $this->tablerefs[$table_index];
	 }
	 /*
	    Find the name of a database given its index
	    @param int $database_index
	    @return string $database_name:
	    @throw DatabaseNotFoundException
	 */
	 public function find_database_name(int $database_index) : string{
	 	 if($database_index < 0 || $database_index >= count($this->databases)){
	 	 	 throw new DatabaseNotFoundException((Object)[
	 	 	     'database_name'  => "",
	 	 	     'database_index' => $database_index,
	 	 	     'databases'      => $this->databases
	 	     ]);
	 	 }
	 	 return $this->databases[$database_index];
	 }

}
?>