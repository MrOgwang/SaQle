<?php
declare(strict_types = 1);

namespace SaQle\Dao\Model\Manager\Traits;

use SaQle\Dao\Join\Interfaces\IJoinManager;
use SaQle\Services\Container\Cf;

trait Join{
	 /**
 	 * The join manager handles joins
 	 * */
 	 private ?IJoinManager $jmanager = null;

 	 /**
 	  * Set the join manager
 	  * */
 	 protected function set_join_manager(){
 	 	if(is_null($this->jmanager)){
 	 		$this->jmanager = Cf::create(IJoinManager::class);
 	 	}
 	 }

 	 /**
     * Get the join manager
     * @return JoinManager
     */
     protected function get_join_manager() : IJoinManager{
     	 $this->set_join_manager();
     	 return $this->jmanager;
     }

     protected function get_join_clause(){
	 	 $join_manager = $this->get_join_manager();
	 	 $join_manager->set_context_tracker($this->get_context_tracker());
	 	 return $join_manager->construct_join_clause();
	 }

      /**
      * Add a join
      * @param string          $type:     the type of join to do
      * @param string          $table:    the name of the table to join.
      * @param nullable string $from:     the name of the field of primary table connecting to the joining table
      * @param nullable string $to:       the name of teh field of joining table conecting to primary table.
      * @param nullable string $as:       the aliase name for the joining table.
      * @param nullable string $ref:      the ref name for the joining table
      * @param nullable array  $select:   aan array of joining table fields to select
      * @param nullable string $database: the name of the database in which the joining table belongs
      * */
     private function add_join(
     	 string  $type, 
     	 string  $table, 
     	 ?string $from     = null, 
     	 ?string $to       = null, 
     	 ?string $as       = null, 
     	 ?string $ref      = null,
     	 ?array  $select   = null,
     	 ?string $database = null
     ){
     	 $this->register_joining_model(table: $table, tblref: $ref, as: $as);

     	 $database = $database ?: $this->get_context_tracker()->find_database_name(0);
         $ptable   = $this->get_context_tracker()->find_table_name(0);
     	 $model    = $this->get_model($ptable);
		 $pkname   = $model->get_pk_name();
     	 $from     = $from ?: $pkname;
     	 $to       = $to   ?: $pkname;
     	 $this->get_join_manager()->add_join(type: $type, table: $table, from: $from, to: $to, as: $as, ref: $ref, database: $database);
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
	 	 ?array  $select   = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'INNER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database);
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
	 	 ?array  $select   = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database);
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
	 	 ?array  $select   = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'LEFT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database);
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
	 	 ?array  $select   = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'RIGHT OUTER JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database);
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
	 	 ?array  $select   = null,
	 	 ?string $database = null
	 ){
	 	 $this->add_join(type: 'FULL OUTRE JOIN', table: $table, from: $from, to: $to, as: $as, ref: $ref, select: $select, database: $database);
	     return $this;
	 }

     /**
      * Get the names of the table and the database for model
      * @param string $model: the class name of model
      * */
     private function get_table_n_database(string $model){
     	 [$db_class, $table_name] = $model::get_schema()->get_table_n_dbcontext();
	 	 $database_name = DB_CONTEXT_CLASSES[$this->dbclass]['name'];
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

}
?>