<?php
 declare(strict_types = 1);
 namespace SaQle\Dao\Model\Manager;
 require_once __DIR__."/../../../exceptions/exceptions.php";

 use SaQle\Http\Request\Request;
 use SaQle\Dao\Filter\Manager\Interfaces\IFilterManager;
 use SaQle\Dao\Filter\Parser\Interfaces\IFilterParser; 
 use SaQle\Dao\Join\Manager\JoinManager;
 use SaQle\Dao\Limit\Manager\ILimitManager;
 use SaQle\Dao\Order\Manager\IOrderManager;
 use SaQle\Dao\Select\Manager\ISelectManager;
 use SaQle\Dao\Formatter\IDataFormatter;
 use SaQle\Dao\Model\Interfaces\ITableSchema;
 use SaQle\Dao\Connection\Interfaces\IConnection;
 use SaQle\Dao\DbContext\Trackers\DbContextTracker;
 use SaQle\Dao\DbContext\Attributes\IDbContextOptions;
 use function SaQle\Exceptions\{modelnotfoundexception};
 use SaQle\Security\Models\ModelValidator;
 use SaQle\Security\Security;
 use SaQle\Dao\Field\Exceptions\FieldValidationException;
 use SaQle\Dao\Model\Model;
     
 abstract class IModelManager{
     /**
     * A key => value array of models(tables) and respective model classes attached to the current context of this model manager
     * @var array
     */
 	 protected array  $_model_references;
 	 protected array  $_insert_data_container;
 	 protected array  $_update_data_container;
 	 protected array  $_file_data = [];
 	 protected string $current_dbcontext_class;
 	 protected array  $_models = [];
 	 protected bool   $_is_operation_aborted = false;
 	 protected array  $_operation_status = [];

 	 /**
 	  * Whether to ignore soft delete filter on fectched results
 	  * @var bool
 	  * */
 	 protected bool $_ignore_soft_delete = false;

 	 /**
 	 * Create a new model manager instance
 	 * @param IFilterManager
 	 * @param IFilterParser
 	 */
	 public function __construct(
	 	 protected Request           $request, //can be made a singleton
	     protected IFilterManager    $fmanager, 
		 private   DbContextTracker  $_context_tracker,
		 private   JoinManager       $_join_manager,
		 private   ILimitManager     $_limit_manager,
		 private   IOrderManager     $_order_manager,
		 private   ISelectManager    $_select_manager,
		 private   IDataFormatter    $_data_formatter, //can be removed entirely
		 private   IConnection       $_connection, //can be madea singleton
	 ){
	 	$this->_insert_data_container = ["prmkeytype" => "", "data" => [], "prmkeyname" => "", "prmkeyvalues" => [], "navigationkeys" => []];
	 	$this->_update_data_container = ["data" => []];
	 }

	 //setters
	 /**
	  * Set the current db context class
	  * */
	 public function set_dbcontext_class(string $dbcontext_class){
	 	$this->current_dbcontext_class = $dbcontext_class;
	 }

	 /**
	 * Set the current model references
	 * @param array
	 */
	 public function set_model_references(array $refs){
	 	 $this->_model_references = $refs;
	 }

	 /**
	 * Set the database context options.
	 * @param IDbContextOptions
	 */
	 public function set_context_options(IDbContextOptions $context_options){
	 	 $this->_connection->set_context_options($context_options);
	 }

	 /**
	 * Set the database context tracker
	 * @param DbContextTracker
	 */
	 public function set_context_tracker(DbContextTracker $_context_tracker){
	 	$this->_context_tracker = $context_tracker;
	 }

	 //getters
	 /**
	  * Get current request object
	  * */
	 public function get_request(){
	 	return $this->request;
	 }

	 /**
	  * Get the current db context class
	  * */
	 public function get_dbcontext_class() : string{
	 	 return $this->current_dbcontext_class;
	 }

	 /**
	 * Get the database context options.
	 * @return IDbContextOptions
	 */
	 public function get_context_options() : IDbContextOptions{
	 	 return $this->_connection->get_context_options();
	 }

	 /**
	 * Get tje database context tracker
	 * @return DbContextTracker
	 */
	 public function get_context_tracker() : DbContextTracker{
	 	 return $this->_context_tracker;
	 }

	 /**
	 * Get the model references
	 * @return DbContextTracker
	 */
	 public function get_model_references() : array{
	 	 return $this->_model_references;
	 }

     /**
	 * Get the limit manager
	 * @return ILimitManager
	 */
	 protected function get_limit_manager() : ILimitManager{
     	 return $this->_limit_manager;
     }

     /**
	 * Get the order manager
	 * @return IOrderManager
	 */
	 protected function get_order_manager() : IOrderManager{
     	 return $this->_order_manager;
     }

     /**
     * Get the join manager
     * @return JoinManager
     */
     protected function get_join_manager() : JoinManager{
     	return $this->_join_manager;
     }

     /**
     * Get the data formatter
     * @return IDataFormatter
     */
     protected function get_data_formatter() : IDataFormatter{
     	return $this->_data_formatter;
     }

     /**
     * Get a single model object from name
     * @param string $name
     * @return Model
     */
     protected function get_model(string $name) : ITableSchema{
     	$model_class = $this->_model_references[$name];
     	$state = $model_class::state();
		$state->set_request($this->request);
		return $state;
     }

     /**
     * Get the connection
     * @return IConnection
     */
     protected function get_connection() : IConnection{
     	return $this->_connection;
     }

     /**
     * Get the select manager
     * @return ISelectManager
     */
     protected function get_select_manager() : ISelectManager{
     	return $this->_select_manager;
     }

     // utilities

     /**
      * Initilialize a model manager
      * */
     public function initialize(string $table_name, ?string $dbcontext_class = null, ?string $model_class = null){
     	 if($dbcontext_class){
     	 	 $model_refs = $model_class ? array_merge($dbcontext_class::get_models(), [$table_name => $model_class]) : $dbcontext_class::get_models();
     	 	 $this->set_model_references($model_refs);
             $this->set_dbcontext_class($dbcontext_class);
     	 }
     	 $this->register_joining_model(table: $table_name);
     }

     /**
     * Register to context tracker
     * @param string $table_name
     * @param string $table_aliase
     * @param string $database_name
     * @param array  $field_list
     */
     public function register_to_context_tracker(string $table_name, string $table_aliase, string $database_name, array $field_list){
	 	 $this->_context_tracker->add_table($table_name);
	 	 $this->_context_tracker->add_aliase($table_aliase);
	 	 $this->_context_tracker->add_database($database_name);
	 	 $this->_context_tracker->add_fields($field_list);
	 }

	 /**
	 * Do a join of tables:
	 * @param string $table: the name of the table to join in current query
	 * @param string $from:  the name of the field of parent table.
	 * @param string $to:    the name of the field of joining table
	 * @param string $as:    the aliase name of the joining table.
	 */
	 public function register_joining_model(string $table, ?string $as = null){
	 	 $model_references = $this->get_model_references();
	 	 modelnotfoundexception($table, $model_references, $this->get_context_options()->get_name());
		 #register model info with the context tracker
		 $this->register_to_context_tracker(
		 	 table_name:    $table,
		 	 table_aliase:  !is_null($as) ? $as : "",
		 	 database_name: $this->get_context_options()->get_name(),
		 	 field_list:    $this->get_model($table)->get_field_names()
		 );
	 }

     /**
     * Limit the number of rows returned by a select query.
     * @param int $page - the page to fetch
     * @param int records - the number of records to fetch.
     */
	 protected function set_limit(int $page = 1, int $records = 10){
	 	$this->_limit_manager->set_limit(page: $page, records: $records);
	 }

	 /**
     * Order the results returned by a select query.
     * @param array $fields - the field names to order based on
     * @param string $direction - order ASC or DESC
     */
	 protected function set_order(array $fields, string $direction = "ASC"){
	 	$this->_order_manager->set_order(fields: $fields, direction: $direction);
	 }

     public function get_raw_filter(){
     	return $this->fmanager->get_raw_filter();
     }
     public function get_filter_object(){
     	return $this->fmanager->get_filter_object($this->get_context_tracker());
	 }
	 public function get_parsed_filter(){
     	return $this->fmanager->get_parsed_filter($this->get_context_tracker());
	 }
}
?>