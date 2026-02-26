<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Join\JoinBuilder;
use SaQle\Orm\Query\Helpers\Q;

trait JoinManager{
	 /**
     * The group query builder
     * */
     public protected(set) JoinBuilder $jbuilder {
         set(JoinBuilder $value){
             $this->jbuilder = $value;
         }

         get => $this->jbuilder;
     }

     public function __construct(){
         $this->jbuilder = new JoinBuilder();
     }

      /**
      * Add a join
      * @param string          $type:     the type of join to do
      * @param string          $table:    the name of the table to join.
      * @param nullable string $from:     the name of the field of primary table connecting to the joining table
      * @param nullable string $to:       the name of teh field of joining table conecting to primary table.
      * @param nullable string $as:       the aliase name for the joining table.
      * @param nullable string $ref:      the ref name for the joining table
      * @param nullable Q      $q:        introduce a filter with Q
      * @param nullable string $database: the name of the database in which the joining table belongs
      * */
     private function add_join(
     	 string  $type, 
     	 string  $table, 
     	 ?string $from     = null, 
     	 ?string $to       = null, 
     	 ?string $as       = null, 
     	 ?string $ref      = null,
     	 ?Q      $q        = null,
     	 ?string $database = null
     ){
     	 $this->before_join();

     	 $joining_model = $this->register_joining_model(table: $table, tblref: $ref, as: $as);

     	 $database = $database ?: $this->query_reference_map->find_database_name(0);
         $ptable   = $this->query_reference_map->find_table_name(0);
     	 $model    = $this->model_from_table($ptable);
		 $pkname   = $model->get_pk_name();
     	 $from     = $from ?: $pkname;
     	 $to       = $to   ?: $pkname;
     	 $this->jbuilder->add_join(type: $type, table: $table, from: $from, to: $to, as: $as, ref: $ref, database: $database, query: $q, model: $joining_model::class);

     	 $this->after_join();
     }

     /**
      * Do an inner join - parameters are as explained in add_join above
      * */
	 public function inner_join(
	 	 string  $table, 
	 	 ?string $from     = null, 
	 	 ?string $to       = null, 
	 	 ?string $as       = null, 
	 	 ?string $ref      = null,
	 	 ?Q      $q        = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'INNER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, q: $q, database: $database);
	     return $this;
	 }

	 /**
	  * Do an outer join - parameters are as explained in add_join above
	  * */
	 public function outer_join(
	 	 string  $table, 
	 	 ?string $from     = null, 
	 	 ?string $to       = null, 
	 	 ?string $as       = null, 
	 	 ?string $ref      = null,
	 	 ?Q      $q        = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, q: $q, database: $database);
	     return $this;
	 }

	 /**
	  * Do an left outer join - parameters are as explained in add_join above
	  * */
	 public function left_outer_join(
	 	 string  $table, 
	 	 ?string $from     = null, 
	 	 ?string $to       = null, 
	 	 ?string $as       = null, 
	 	 ?string $ref      = null,
	 	 ?Q      $q        = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'LEFT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, q: $q, database: $database);
	     return $this;
	 }

     /**
	  * Do an right outer join - parameters are as explained in add_join above
	  * */
	 public function right_outer_join(
	 	 string  $table, 
	 	 ?string $from     = null, 
	 	 ?string $to       = null, 
	 	 ?string $as       = null, 
	 	 ?string $ref      = null,
	 	 ?Q      $q        = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'RIGHT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, q: $q, database: $database);
	     return $this;
	 }

     /**
	  * Do an full outer join - parameters are as explained in add_join above
	  * */
	 public function full_outer_join(
	 	 string  $table, 
	 	 ?string $from     = null, 
	 	 ?string $to       = null, 
	 	 ?string $as       = null, 
	 	 ?string $ref      = null,
	 	 ?Q      $q        = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'FULL OUTRE JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, q: $q, database: $database);
	     return $this;
	 }

     /**
      * Get the names of the table and the database for model
      * @param string $model: the class name of model
      * */
     private function get_table_n_database(string $model){
     	 [$db_class, $table_name] = $model::get_table_and_connection();
	 	 $database_name = config('db.connections')[$this->model->table->get_connection_name()]['database'];
	 	 return [$table_name, $database_name];
     }

     /**
      * Do an inner join with a model class name
      * @param string $model: the model class name to join
      * */
	 public function inner_join_with_model(
	 	 string  $model, 
	 	 ?string $from = null, 
	 	 ?string $to = null, 
	 	 ?string $as = null, 
	 	 ?string $ref = null, 
	 	 ?array  $select = null
	 ){
	 	 [$table_name, $database_name] = $this->get_table_n_database($model);
	 	 return $this->inner_join(table: $table_name, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database_name);
	 }

	 protected function before_join(){

	 }

	 protected function after_join(){
	 	
	 }

}
