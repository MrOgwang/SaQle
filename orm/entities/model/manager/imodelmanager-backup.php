<?php
 declare(strict_types = 1);

 namespace SaQle\Orm\Entities\Model\Manager;

 require_once __DIR__."/../../../../exceptions/exceptions.php";

 use SaQle\Http\Request\Request;
 use SaQle\Orm\Entities\Model\Interfaces\ITableSchema;
 use SaQle\Orm\Connection\Interfaces\IConnection;
 use SaQle\Orm\Connection\Connection;
 use SaQle\Orm\Database\Trackers\DbContextTracker;
 use SaQle\Orm\Database\Attributes\IDbContextOptions;
 use function SaQle\Exceptions\modelnotfoundexception;
 use SaQle\Orm\Entities\Model\Manager\Modes\FetchMode;
 use SaQle\Orm\Commands\ICommand;
 use SaQle\Services\Container\{Cf, ContainerService};

 use SaQle\Orm\Query\Helpers\{JoinManager, FilterManager, LimitManager, OrderManager, SelectManager, GroupManager};
     
 abstract class IModelManager{
 	 //use Join, Filter, Limit, Order, Select, Group;

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
 	private string $dbclass;

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

     /**
     * This is an array that contains information about the data to be inserted and the data itself. Has the following keys.
     * @var string prmkeytype:     the type of primary key, can be AUTO or GUID
     * @var array  data:           an array of data to be inserted during an insert operation
     * @var string prmkeyname:     the name of the primary key column of the table being manipulated
     * @var array  prmkeyvalues:   an array of GUID/INT values for the data to be inserted
     * @var array  navigationkeys: an array of names for the navigational keys in the table that is being curremtly manipulated
     */
 	 protected array $insert_data_container = ["prmkeytype" => "", "data" => [], "prmkeyname" => "", "prmkeyvalues" => [], "navigationkeys" => [], 'multiple' => false];

 	 /**
 	  * This an array that contains information about the data to be updated and the update data itself.
 	  * */
 	 protected array  $update_data_container = ["data" => []];

 	 /**
 	  * This is the container that keeps all the files to be added in an uopdate operation or an insert operation
 	  * */
 	 protected array $file_data = [];

 	 /**
 	  * This is a container that keeps track of duplicates during an update or insert operation. Has the following keys:
 	  * @var bool is_duplicate:       whether the update or insert data contains duplicate or not
 	  * @var array duplicate_entries: if the update or insert data has duplicates, they will be stored here
 	  * @var array unique_fields:     an array of field names considered unique and used to determine whether there are duplicate recorda sor not
 	  * @var bool unique_together:    whether the unique fields above should be considered unique together or individually
 	  * */
 	 protected array $operation_status = [];

 	 /**
 	  * This is the state of the object being updated or inserted at the time either of the two operations is requested.
 	  * This is simply a key => value array where the keys are the column/field names
 	  * */
 	 protected ?array $data_state = null;

 	 /**
 	  * Whether to return the model representation of the fetched object or a simple stdClass object
 	  * */
 	 private bool $ctomodel = false;

 	 /**
 	  * The fetch mode: whether to fetch deleted rows only, non deleted rows only or both
 	  * */
 	 private FetchMode $fetchmode = FetchMode::NON_DELETED;

     /**
      * Operation to execute command object
      * */
 	 protected ICommand $crud_command;

     /**
      * Create a new model manager instance.
      * @param Request          $request:    The current request object
      * @param DbContextTracker $tracker:    Keeps track of all the tables and fields referenced in the operation being performed
      * @param IConnection      $connection: The connection to the database
      * */
	 public function __construct(protected Request $request, protected DbContextTracker $ctxtracker){
	 	 $this->__orderConstruct();
	 	 $this->__joinConstruct();
	 	 $this->__filterConstruct();
	 	 $this->__limitConstruct();
	 	 $this->__selectConstruct();
	 	 $this->__groupConstruct();
	 }

	 /**
	  * Set sql and data
	  * */
	 public function sqlndata(string $sql, ?array $data = null){
	 	 $this->sqldata = ['sql' => $sql, 'data' => $data];
	 	 return $this;
	 }

	 /**
	  * Set configurations
	  * */
	 public function config(...$configurations){
	 	 $this->configurations = array_merge($this->configurations, $configurations);
	 	 return $this;
	 }

     /**
      * Set to model
      * */
	 public function tomodel(bool $tomodel = true){
	 	 $this->ctomodel = $tomodel;
	 	 return $this;
	 }

	 /**
	  * Set the current db context class
	  * */
	 public function set_dbcontext_class(string $dbclass){
	 	$this->dbclass = $dbclass;
	 }

	  /**
      * Set the fetch mode, whether to get all rows, no deleted rows only or deleted rows only
      * */
     public function withdeleted(FetchMode $mode){
	 	 $this->fetchmode = $mode;
	 	 return $this;
	 }

	 /**
	 * Set the database context tracker
	 * @param DbContextTracker
	 */
	 public function set_context_tracker(DbContextTracker $_context_tracker){
	 	$this->ctxtracker = $context_tracker;
	 }

     //getters

     /**
	  * Get sql and data
	  * */
	 public function get_sqlndata(){
	 	 return $this->sqldata;
	 }

	 public function is_custom_sql(){
	 	 return $this->sqldata['sql'];
	 }

     /**
      * Get configurations
      * */
     public function get_configurations(){
     	 return $this->configurations;
     }

	 /**
	  * Get to model
	  * */
	 public function get_tomodel(){
	 	 return $this->ctomodel;
	 }

	 /**
	  * Get the current db context class
	  * */
	 public function get_dbcontext_class() : string{
	 	 return $this->dbclass;
	 }

	 /**
	  * Get the fetch mode
	  * */
	 public function fetch_mode(){
	 	return $this->fetchmode;
	 }

	 /**
	  * Get current request object
	  * */
	 public function get_request(){
	 	return $this->request;
	 }

	 /**
	 * Get the database context options.
	 * @return IDbContextOptions
	 */
	 public function get_context_options() : IDbContextOptions{
	 	 return Cf::create(ContainerService::class)->createDbContextOptions(...DB_CONTEXT_CLASSES[$this->dbclass]);
	 }

	 /**
	 * Get tje database context tracker
	 * @return DbContextTracker
	 */
	 public function get_context_tracker() : DbContextTracker{
	 	 return $this->ctxtracker;
	 }

     /**
     * Get a single model object from name
     * @param string $name
     * @return Model
     */
     protected function get_model(string $name) : ITableSchema{
     	$refs = array_merge($this->dbclass::get_models(), $this->tmodels);
     	$model_class = $refs[$name];
     	$state = $model_class::state();
		return $state;
     }

     /**
     * Get the connection
     * @return IConnection
     */
     protected function get_connection() : IConnection{
     	 return Connection::make(Cf::create(ContainerService::class)->createDbContextOptions(...DB_CONTEXT_CLASSES[$this->dbclass]));
     }

     // utilities

     /**
      * Initilialize a model manager
      * */
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
		 	 database_name: $this->get_context_options()->get_name(),
		 	 field_list:    $meta->actual_column_names,
		 	 ff_settings:   $meta->file_required_fields,
		 	 table_ref:     $tblref
		 );
	 }

     private function get_select_sql_info(){
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

     

	 public function sql_info(string $operation){
	 	 return match($operation){
	 	 	 'select' => $this->get_select_sql_info(),
	 	 	 'insert' => $this->get_insert_sql_info()
	 	 };
	 }
}
?>