<?php
namespace SaQle\Orm\Query\Join;

class Join{

     /**
      * The join type
      * inner_join, outer_join, full_outer_join and so forth
      * */
	 public private(set) string $type {
	 	 set(string $value){
	 	 	 $this->type = $value;
	 	 }

	 	 get => $this->type;
	 }

     /**
      * The name of the table that is joining
      * 
      * TODO: Enable joins using the model class name only
      * */
	 public private(set) string $table {
	 	 set(string $value){
	 	 	 $this->table = $value;
	 	 }

	 	 get => $this->table;
	 }

	 /**
	  * The name of the column on the parent table to tie the joining table to
	  * */
	 public private(set) ?string $from = null {
	 	 set(?string $value){
	 	 	 $this->from = $value;
	 	 }

	 	 get => $this->from;
	 }

	 /**
	  * The name of the column on the joining table to tie the parent table to
	  * */
	 public private(set) ?string $to = null {
	 	 set(?string $value){
	 	 	 $this->to = $value;
	 	 }

	 	 get => $this->to;
	 }

	 /**
	  * The name of the database in which the joining table lives:
	  * 
	  * Note: Essentially for tables to be joined the assumption is that they live in the same database,
	  * but sometimes a join is possible even if two tables live in different databases as long as they
	  * are on the same server.
	  * */
	 public private(set) ?string $database = null {
	 	 set(?string $value){
	 	 	 $this->database = $value;
	 	 }

	 	 get => $this->database;
	 }

	 /**
	  * The table name aliase to give to the joining table
	  * */
	 public private(set) ?string $aliase = null {
	 	 set(?string $value){
	 	 	 $this->aliase = $value;
	 	 }

	 	 get => $this->aliase;
	 }

	 /**
	  * The table reference for the joining table.
	  * 
	  * Table references are a bit of a dirty hack to enable some feature to work
	  * in the model manager when fetching navigational fields. 
	  * 
	  * What you need to know is that a table reference will have priority over table aliase and table name
	  * when constructing the join clause.
	  * */
	 public private(set) ?string $ref = null{
	 	 set(?string $value){
	 	 	 $this->ref = $value;
	 	 }

	 	 get => $this->ref;
	 }
	 
	 public function __construct(string $type, string $table, string $from, string $to, ?string $database = null, ?string $aliase = null, ?string $ref = null){
	 	 $this->type     = $type;
	 	 $this->table    = $table;
	 	 $this->from     = $from;
	 	 $this->to       = $to;
	 	 $this->database = $this->database;
	 	 $this->aliase   = $this->aliase;
	 	 $this->ref      = $this->ref;
	 }
}
