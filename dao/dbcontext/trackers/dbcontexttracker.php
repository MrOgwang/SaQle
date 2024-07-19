<?php
declare(strict_types = 1);

namespace SaQle\Dao\DbContext\Trackers;

use SaQle\Dao\DbContext\Trackers\Exceptions\TableNotFoundException;
use SaQle\Dao\DbContext\Trackers\Exceptions\DatabaseNotFoundException;
use SaQle\Dao\DbContext\Trackers\Interfaces\IDbContextTracker;

class DbContextTracker implements IDbContextTracker{
	 public function __construct(
	 	 private array $_tables    = [],//a list of all the table names that will be rerenced in this query.
	     private array $_aliases   = [],//a list of all the table name aliases that will be refenced in this query.
	     private array $_databases = [],//a list of all the databases that will be refenced in this query.
	     private array $_fieldrefs = []//an array of all tables and respective fields as referenced in this query.
	 ){
		 
	 }
	 public function add_table(string $table_name){
	 	$this->_tables[] = $table_name;
	 }
	 public function add_aliase(string $table_aliase){
	 	$this->_aliases[] = $table_aliase;
	 }
	 public function add_database(string $database_name){
	 	$this->_databases[] = $database_name;
	 }
	 public function add_fields(array $field_list){
	 	$this->_fieldrefs[] = $field_list;
	 }


	 public function get_tables(){
	 	return $this->_tables;
	 }
	 public function get_aliases(){
	 	return $this->_aliases;
	 }
	 public function get_databases(){
	 	return $this->_databases;
	 }
	 public function get_fieldrefs(){
	 	return $this->_fieldrefs;
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
		 return ['table_index' => array_search($table_name, $this->_tables, true), 'name_changed' => $table_name_changed];
	 }
	 /*
	    Find the name of a table given its index
	    @param int $table_index
	    @return string $table_name:
	    @throw TableNotFoundException
	 */
	 public function find_table_name(int $table_index) : string{
	 	 if($table_index < 0 || $table_index >= count($this->_tables)){
	 	 	 throw new TableNotFoundException((Object)[
	 	 	     'table_name'  => "",
	 	 	     'table_index' => $table_index,
	 	 	     'tables'      => $this->_tables
	 	     ]);
	 	 }
	 	 return $this->_tables[$table_index];
	 }
	 /*
	    Find the aliase of a table given its index
	    @param int $table_index
	    @return string $table_aliase:
	    @throw TableNotFoundException
	 */
	 public function find_table_aliase(int $table_index) : string{
	 	 if($table_index < 0 || $table_index >= count($this->_tables)){
	 	 	 throw new TableNotFoundException((Object)[
	 	 	     'table_name'  => "",
	 	 	     'table_index' => $table_index,
	 	 	     'tables'      => $this->_tables
	 	     ]);
	 	 }
	 	 return $this->_aliases[$table_index];
	 }
	 /*
	    Find the name of a database given its index
	    @param int $database_index
	    @return string $database_name:
	    @throw DatabaseNotFoundException
	 */
	 public function find_database_name(int $database_index) : string{
	 	 if($database_index < 0 || $database_index >= count($this->_databases)){
	 	 	 throw new DatabaseNotFoundException((Object)[
	 	 	     'database_name'  => "",
	 	 	     'database_index' => $database_index,
	 	 	     'databases'      => $this->_databases
	 	     ]);
	 	 }
	 	 return $this->_databases[$database_index];
	 }

}
?>