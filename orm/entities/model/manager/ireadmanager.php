<?php
 declare(strict_types = 1);

 namespace SaQle\Orm\Entities\Model\Manager;

 require_once __DIR__."/../../../../exceptions/exceptions.php";

 use SaQle\Orm\Entities\Model\Interfaces\ITableSchema;
 use SaQle\Orm\Database\Trackers\DbContextTracker;
 use SaQle\Orm\Database\Attributes\IDbContextOptions;
 use SaQle\Orm\Entities\Model\Manager\Modes\FetchMode;
 use SaQle\Orm\Query\Helpers\{JoinManager, FilterManager, LimitManager, OrderManager, SelectManager, GroupManager};
     
 abstract class IReadManager{
 	 use JoinManager{
 	 	 JoinManager::__construct as private __joinConstruct;
 	 }
	 use FilterManager{
		 FilterManager::__construct as private __filterConstruct;
	 }
	 use LimitManager{
		 LimitManager::__construct as private __limitConstruct;
	 }
	 use OrderManager{
		 OrderManager::__construct as private __orderConstruct;
	 }
	 use SelectManager{
		 SelectManager::__construct as private __selectConstruct;
	 }
	 use GroupManager{
		 GroupManager::__construct as private __groupConstruct;
	 }

 	 /**
 	 * a key => value array of configuration parameters
 	 * */
 	 protected array $configurations = [
 		/**
 		 * field name qualification mode: how to qualify field names in the resulting sql statement: options, 
 		 * 'F-QUALIFY' - the field name is qualified to database_name.table_name.field_name
 		 * 'H-QUALIFY' - the field name is qualified to table_name.field_name, 
 		 * 'N-QUALIFY' - the field name is never qualified and is used as is
 		 * */
 		 'fnqm' => 'F-QUALIFY',

 		 /**
 		  * from table naming mode: how the from table clause is named. options
 		  * 'N-WITH-A' - name and aliase, if an aliase is avaialbe
 		  * 'N-ONLY'   - use only the table name, even if an aliase is provided
 		  * 'A-ONLY'   - use only the aliase name, even if the table name is provided
 		  * */
 		 'ftnm' => 'N-WITH-A',

 		 /**
 		 * from table qualification mode: how the from table clause is named. options
 		 * 'F-QUALIFY' - the table name is qualified to database_name.table_name, 
 		 * 'N-QUALIFY' - the table name is never qualified and is used as is
 		  * */
 		 'ftqm' => 'F-QUALIFY'
 	 ];

 	/**
 	 * Sometimes you have an sql and data that you want to execute instead of constructing one via the manager.
 	 * When this is set, it overrides the one automatically constructed via the model manager.
 	 * */
 	private array $sqldata = [
 		'sql' => '',
 		'data' => null
 	];

 	/**
 	 * The db context class being manipulated
 	 * */
 	protected string $dbclass;

 	 /**
 	 * Through models are not explicitly defined in the db context class. This is s separate reference
 	 * for them in the model manager.
 	 * */
 	 public protected(set) array $tmodels = [] {
 		 set(array $value){
 		 	 $this->tmodels = array_merge($this->tmodels, $value);
 		 }

 		 get => $this->tmodels;
 	 }

 	 //The fetch mode: whether to fetch deleted rows only, non deleted rows only or both
 	 protected FetchMode $fetchmode = FetchMode::NON_DELETED;

 	 protected DbContextTracker $ctxtracker {
 	 	 set(DbContextTracker $value){
 		 	 $this->ctxtracker = $value;
 		 }

 		 get => $this->ctxtracker;
 	 }

	 public function __construct(){
	 	 $this->ctxtracker = new DbContextTracker();
	 	 $this->__orderConstruct();
	 	 $this->__joinConstruct();
	 	 $this->__filterConstruct();
	 	 $this->__limitConstruct();
	 	 $this->__selectConstruct();
	 	 $this->__groupConstruct();
	 }

	 //Set sql and data
	 public function sqlndata(string $sql, ?array $data = null){
	 	 $this->sqldata = ['sql' => $sql, 'data' => $data];
	 	 return $this;
	 }

	 //Set configurations
	 public function config(...$configurations){
	 	 $this->configurations = array_merge($this->configurations, $configurations);
	 	 return $this;
	 }

	 //Set the current db context class
	 public function set_dbcontext_class(string $dbclass){
	 	$this->dbclass = $dbclass;
	 }

     //Get sql and data
	 public function get_sqlndata(){
	 	 return $this->sqldata;
	 }

	 public function is_custom_sql(){
	 	 return $this->sqldata['sql'];
	 }

     //Get configurations
     public function get_configurations(){
     	 return $this->configurations;
     }

	 //Get the database context tracker
	 public function get_context_tracker() : DbContextTracker{
	 	 return $this->ctxtracker;
	 }

     //Get a single model object from name
     protected function get_model(string $name) : ITableSchema {
     	 $refs = array_merge($this->dbclass::get_models(), $this->tmodels);
     	 $model_class = $refs[$name];
     	 return $model_class::state();
     }

     //Initilialize a model manager
     public function initialize(string $table_name, ?string $dbcontext_class = null, ?string $model_class = null, ?string $table_aliase = null, 
     	?string $table_ref = null){
     	 if($dbcontext_class){
     	 	 if($model_class){
     	 	 	 $this->tmodels = [$table_name => $model_class];
     	 	 }
             $this->set_dbcontext_class($dbcontext_class);
     	 }
     	 $this->register_joining_model(table: $table_name, tblref: $table_ref, as: $table_aliase);
     }

     /**
     * Register to context tracker
     * @param string $table_name
     * @param string $table_aliase
     * @param string $database_name
     * @param array  $field_list
     */
     public function register_to_context_tracker(string $table_name, string $table_aliase, string $database_name, array $field_list, array $ff_settings, ?string $table_ref = null){
	 	 $this->ctxtracker->tables     = array_merge($this->ctxtracker->tables,     [$table_name]);
	 	 $this->ctxtracker->aliases    = array_merge($this->ctxtracker->aliases,    [$table_aliase]);
	 	 $this->ctxtracker->tablerefs  = array_merge($this->ctxtracker->tablerefs,  [$table_ref]);
	 	 $this->ctxtracker->databases  = array_merge($this->ctxtracker->databases,  [$database_name]);
	 	 $this->ctxtracker->fieldrefs  = array_merge($this->ctxtracker->fieldrefs,  [$field_list]);
	 	 $this->ctxtracker->ffsettings = array_merge($this->ctxtracker->ffsettings, [$ff_settings]);
	 }

	 /**
	 * Register a model to context tracker
	 * @param string $table: the name of the table to join in current query
	 * @param string $from:  the name of the field of parent table.
	 * @param string $to:    the name of the field of joining table
	 * @param string $as:    the aliase name of the joining table.
	 */
	 public function register_joining_model(string $table, ?string $tblref = null, ?string $as = null){
	 	 $meta = $this->get_model($table)->meta;
		 $this->register_to_context_tracker(
		 	 table_name:    $table,
		 	 table_aliase:  !is_null($as) ? $as : "",
		 	 database_name: DB_CONTEXT_CLASSES[$this->dbclass]['name'],
		 	 field_list:    $meta->actual_column_names,
		 	 ff_settings:   $meta->file_required_fields,
		 	 table_ref:     $tblref
		 );
	 }

     public function get_select_sql_info(){
	 	 $where_clause = $this->wbuilder->get_where_clause($this->ctxtracker, $this->get_configurations());
     	 $data         = $where_clause->data;
		 $select       = $this->get_selected();
		 $database     = $this->ctxtracker->find_database_name(0);
		 $table        = $this->ctxtracker->find_table_name(0);
		 $table_aka    = $this->ctxtracker->find_table_aliase(0);
		 $table_ref    = $this->ctxtracker->find_table_refernce(0);
         
         $sql          = "SELECT {$select} FROM ";
         $from_ref     = "";
		 if($this->configurations['ftnm'] === 'N-WITH-A'){ //use name and aliase
		 	 $from_ref = $table_ref ?? ($this->configurations['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
		 	 if($table_aka){
		 	     $from_ref .= " AS ".$table_aka;
		     }
		 }elseif($this->configurations['ftnm'] === 'N-ONLY'){ //use only the table name
		 	 $from_ref = $table_ref ?? ($this->configurations['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
		 }elseif($this->configurations['ftnm'] === 'A-ONLY'){ //use only the aliase name
		 	 $from_ref = $table_ref ?? ($this->configurations['ftqm'] === 'F-QUALIFY' ? $database.".".$table : $table);
		 	 $from_ref = $table_aka ? $table_aka : $from_ref;
		 }

		 $sql         .= $from_ref;
		 
		 $sql         .= $this->jbuilder->construct_join_clause($this->ctxtracker);
		 $sql         .= $where_clause->clause;
		 $sql         .= $this->get_groupby_clause();
		 $sql         .= $this->obuilder->construct_order_clause();
		 $sql         .= $this->lbuilder->construct_limit_clause();
		 return ['sql' => $sql, 'data' => $data];
     }
}
?>