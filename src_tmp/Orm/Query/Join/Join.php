<?php
namespace SaQle\Orm\Query\Join;

use SaQle\Orm\Query\Helpers\Q;

class Join{

     /**
      * The join type
      * inner_join, outer_join, full_outer_join and so forth
      * */
	 public private(set) string $type;

     /**
      * The name of the table that is joining
      * 
      * TODO: Enable joins using the model class name only
      * */
	 public private(set) string $table;

	 /**
	  * The name of the column on the parent table to tie the joining table to
	  * */
	 public private(set) ?string $from = null;

	 /**
	  * The name of the column on the joining table to tie the parent table to
	  * */
	 public private(set) ?string $to = null;

	 /**
	  * The name of the database in which the joining table lives:
	  * 
	  * Note: Essentially for tables to be joined the assumption is that they live in the same database,
	  * but sometimes a join is possible even if two tables live in different databases as long as they
	  * are on the same server.
	  * */
	 public private(set) ?string $database = null;

	 /**
	  * The table name aliase to give to the joining table
	  * */
	 public private(set) ?string $aliase = null;

	 /**
	  * The table reference for the joining table.
	  * 
	  * Table references are a bit of a dirty hack to enable some feature to work
	  * in the model manager when fetching navigational fields. 
	  * 
	  * What you need to know is that a table reference will have priority over table aliase and table name
	  * when constructing the join clause.
	  * */
	 public private(set) ?string $ref = null;

	 /**
	  * This will be used to build the AND / OR clause to be used together with the ON clause
	  * when constructing joins
	  * */
	 public private(set) ?Q $query = null;

	 /**
	  * The model class name of the joining table. 
	  * Important: This is used to check whether a field is a relation field
	  * on the joining model during eager loading where the relation field is not
	  * in the main table but on the joining table!
	  * */
	 public private(set) ?string $model = null;
	 
	 public function __construct(string $type, string $table, string $from, string $to, ?string $database = null, ?string $aliase = null, ?string $ref = null, ?Q $query = null, ?string $model = null){
	 	 $this->type     = $type;
	 	 $this->table    = $table;
	 	 $this->from     = $from;
	 	 $this->to       = $to;
	 	 $this->database = $database;
	 	 $this->aliase   = $aliase;
	 	 $this->ref      = $ref;
	 	 $this->query    = $query;
	 	 $this->model    = $model;
	 }
}
