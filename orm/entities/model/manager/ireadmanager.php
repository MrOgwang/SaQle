<?php
 declare(strict_types = 1);

 namespace SaQle\Orm\Entities\Model\Manager;

 use SaQle\Orm\Entities\Model\Interfaces\ITableSchema;
 use SaQle\Orm\Query\References\QueryReferenceMap;
 use SaQle\Orm\Entities\Model\Manager\Modes\FetchMode;
 use SaQle\Orm\Query\Helpers\{JoinManager, FilterManager, LimitManager, OrderManager, SelectManager, GroupManager};
 use SaQle\Orm\Entities\Model\Schema\Model;
 use Closure;
     
class IReadManager extends QueryManager {
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

 	 //The fetch mode: whether to fetch deleted rows only, non deleted rows only or both
 	 protected FetchMode $fetchmode = FetchMode::NON_DELETED;

 	 protected QueryReferenceMap $query_reference_map;

	 public function __construct(Model $model, ?string $tablealiase = null, ?string $tableref = null){
	 	 parent::__construct($model);

	 	 $this->query_reference_map = new QueryReferenceMap();

	 	 $this->__orderConstruct();
	 	 $this->__joinConstruct();
	 	 $this->__filterConstruct();
	 	 $this->__limitConstruct();
	 	 $this->__selectConstruct();
	 	 $this->__groupConstruct();

     	 $this->register_joining_model(table: $this->model->meta->table_name, tblref: $tableref, as: $tablealiase);
	 }

	 //Set sql and data
	 public function sqlndata(string $sql, ?array $data = null){
	 	 $this->set_data($data);
	 	 $this->set_sql($sql);
	 	 return $this;
	 }

	 //Set configurations
	 public function config(...$configurations){
	 	 $this->configurations = array_merge($this->configurations, $configurations);
	 	 return $this;
	 }

     //Get configurations
     public function get_configurations(){
     	 return $this->configurations;
     }

	 //Get the database context tracker
	 public function get_query_reference_map() : QueryReferenceMap {
	 	 return $this->query_reference_map;
	 }

     //get a single model object from name
     protected function model_from_table(string $name) : ITableSchema {
     	 $sibling_models = $this->model->get_sibling_models();
     	 $model_class = $sibling_models[$name];
     	 return $model_class::make();
     }

     /**
     * Register to context tracker
     * @param string $table_name
     * @param string $table_aliase
     * @param string $database_name
     * @param array  $field_list
     */
     public function register_to_context_tracker(string $table_name, string $table_aliase, string $database_name, array $field_list, array $ff_settings, ?string $table_ref = null){
	 	 $this->query_reference_map->tables     = array_merge($this->query_reference_map->tables,     [$table_name]);
	 	 $this->query_reference_map->aliases    = array_merge($this->query_reference_map->aliases,    [$table_aliase]);
	 	 $this->query_reference_map->tablerefs  = array_merge($this->query_reference_map->tablerefs,  [$table_ref]);
	 	 $this->query_reference_map->databases  = array_merge($this->query_reference_map->databases,  [$database_name]);
	 	 $this->query_reference_map->fieldrefs  = array_merge($this->query_reference_map->fieldrefs,  [$field_list]);
	 	 $this->query_reference_map->ffsettings = array_merge($this->query_reference_map->ffsettings, [$ff_settings]);
	 }

	 /**
	 * Register a model to context tracker
	 * @param string $table: the name of the table to join in current query
	 * @param string $from:  the name of the field of parent table.
	 * @param string $to:    the name of the field of joining table
	 * @param string $as:    the aliase name of the joining table.
	 */
	 public function register_joining_model(string $table, ?string $tblref = null, ?string $as = null){
	 	 $model = $this->model_from_table($table);
		 $this->register_to_context_tracker(
		 	 table_name:    $table,
		 	 table_aliase:  !is_null($as) ? $as : "",
		 	 database_name: config('connections')[$this->model->meta->connection_name]['database'],
		 	 field_list:    $model->meta->table_column_names,
		 	 ff_settings:   $model->meta->file_required_fields,
		 	 table_ref:     $tblref
		 );

		 return $model;
	 }

	 protected function after_where(string $field_name, $value){
	 	 $this->dbdriver->set_read_query($this);
	 }

	 protected function after_join(){
	 	 $this->dbdriver->set_read_query($this);
	 }

	 protected function after_limit(){
	 	 $this->dbdriver->set_read_query($this);
	 }

	 protected function after_order(){
	 	 $this->dbdriver->set_read_query($this);
	 }

	 protected function after_select(?array $fields = null, ?Closure $callback = null){
	 	 $this->dbdriver->set_read_query($this);
	 }

	 protected function before_select(?array $fields = null, ?Closure $callback = null){
	 	
	 }

	 protected function after_group(){
	 	 $this->dbdriver->set_read_query($this);
	 }
}
