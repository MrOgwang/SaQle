<?php
 namespace SaQle\Orm\Entities\Model\Manager;

 use SaQle\Core\Exceptions\Model\SelectOperationFailedException;
 use SaQle\Core\Exceptions\Model\NullObjectException;
 use SaQle\Orm\Entities\Model\Schema\Model;
 use SaQle\Commons\{DateUtils, UrlUtils, StringUtils};
 use SaQle\Core\Assert\Assert;
 use SaQle\Orm\Entities\Model\TempId;
 use SaQle\Orm\Entities\Model\Manager\Utils\EventUtils;
 use SaQle\Core\Events\ModelEventPhase;
 use SaQle\Orm\Entities\Model\Manager\Loaders\{RelationStack, EagerLoader};
 use SaQle\Orm\Entities\Model\Collection\{GenericModelCollection, ModelCollection};
 use SaQle\Orm\Entities\Field\Types\Base\RelationField;
 use Exception;
 use Closure;
 use PDO;

final class ReadManager extends IReadManager {
	 use DateUtils, UrlUtils, StringUtils, EventUtils;

	 public function __construct(Model $model, ?string $tablealiase = null, ?string $tableref = null){
	 	 parent::__construct($model, $tablealiase, $tableref);
	 }

	 public function eager_load(){
	 	 return $this->get(stack_active: true);
	 }

	 //return all the rows found
	 public function all(){
	 	 return $this->get();
	 }

	 //return the first row if its available otherwise throw an exception
	 public function first(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	$table = $this->query_reference_map->find_table_name(0);
	 	 	throw new NullObjectException(['table' => $table]);
	 	 }
	 	 return $response[0];
	 }

     //return the first row if its available otherwise return null
	 public function first_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[0] : null;
	 }

     //reteurn the last row if its available otherwise throw an exception
	 public function last(){
	 	 $response = $this->get();
	 	 if(!$response){
	 	 	throw NullObjectException(['table' => $this->query_reference_map->find_table_name(0)]);
	 	 }
	 	 return $response[count($response) - 1];
	 }

	 //return the last row if its available otherwise return null
	 public function last_or_default(){
	 	 $response = $this->get();
	 	 return $response ? $response[count($response) - 1] : null;
	 }

     private function get_auto_includes(Model $model){
	 	$auto_includes = [];
	 	foreach($model->get_fields() as $fn => $fv){
	 		if($fv instanceof RelationField && $fv->is_eager()){
	 			$auto_includes[] = ['relation' => $fv, 'with' => '', 'tuning' => null];
	 		}
	 	}
	 	return $auto_includes;
	 }

	 private function load_related(Model $model_instance, array | ModelCollection $parents, RelationStack $relation_stack){
	 	 $explicit_includes = $this->sbuilder->includes;
	 	 $auto_includes     = $this->get_auto_includes($model_instance);
	 	 $include_instances = array_merge(array_column($explicit_includes, 'relation'), array_column($auto_includes, 'relation'));

	 	 if(!$include_instances){
	 	 	 return $parents;
	 	 }

	 	 $nested_includes = array_merge(array_column($explicit_includes, 'with'), array_column($auto_includes, 'with'));
	 	 $includes_tuning = array_merge(array_column($explicit_includes, 'tuning'), array_column($auto_includes, 'tuning'));

	 	 return (new EagerLoader())->load(
	 	 	 $model_instance->meta->connection_name, 
	 	 	 $parents, 
	 	 	 $include_instances, 
	 	 	 $nested_includes, 
	 	 	 $includes_tuning,
	 	 	 $this->sbuilder,
	 	 	 $relation_stack
	 	 );
	 }

	 private function get(bool $stack_active = false){
	 	 $relation_stack = RelationStack::enter_root();

	 	 //connect to the database
	 	 $this->dbdriver->connect_with_database();

	 	 //get query info
	 	 $query_info = $this->get_query_info();

	 	 //send pre select signal to observers
	 	 $named_args = $this->get_named_args('select', $query_info, $this->model->meta->table_name, $this->model::class);
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::READING, $named_args, resolve('request')->user);

         //execute
         [$statement, $response] = array_values($this->dbdriver->execute($query_info['sql'], $query_info['data']));
         $error_code = $statement->errorCode();

         if($response === false || $error_code !== "00000"){
		 	 throw new SelectOperationFailedException([
		 	 	 'table' => $this->model->meta->table_name, 
		 	 	 'statement_error_code' => $error_code
		 	 ]);
		 }

	 	 $rows = $statement->fetchAll(PDO::FETCH_OBJ);

	 	 //convert rows to model collection first!
	 	 if(!$stack_active && $this->model::class !== "SaQle\Orm\Entities\Model\TempId"){
	 	 	 /*$collection_class = $this->model::class::collection_class();
	 	 	 if($collection_class == GenericModelCollection::class){
	 	 	 	 $rows = $collection_class::from_objects($this->model::class, $rows);
	 	 	 }else{
	 	 	 	 $rows = new $collection_class($rows);
	 	 	 }*/
	 	 }

	 	 //process includes and return
	 	 $result = $this->load_related($this->model, $rows, $relation_stack);

	 	 //send post select signal to observers
	 	 $this->dispatch_event($this->model::class, ModelEventPhase::READ, $named_args, resolve('request')->user, $result);

	 	 RelationStack::leave_root();

 	     return $result;
	 }
	 
	 //includes
	 private function check_with(string $field){
     	 $include_field = $this->model->is_relation_field($field);

     	 if(!$include_field){
     	 	 foreach($this->jbuilder->joins as $j){
     	 	 	 if($j->model){
     	 	 	 	 $j_model = $j->model;
     	 	 	 	 $j_model_instance = $j_model::make();
     	 	 	 	 $include_field = $j_model_instance->is_relation_field($field);

     	 	 	 	 if($include_field){
     	 	 	 	 	 break;
     	 	 	 	 }
     	 	 	 }
     	 	 }
     	 }

     	 if(!$include_field){
     	 	throw new Exception("{$field} This is not an includable field!");
     	 }

     	 return $include_field;
	 }

	 public function with(array|string $field, $callable = null){
	 	 $fields = is_array($field) ? $field : [$field];

	 	 /**
	 	  * @var $callable is either a null, a callable or an array of callables.
	 	  * */
	 	 $callables = [];
	 	 if(!is_array($callable) && !is_null($callable)){
	 	 	 Assert::isCallable($callable, 'Parameter callable must be a callable!');
	 	 	 $callables[$fields[0]] = $callable;
	 	 }elseif(is_array($callable)){ //ensure its an associative array and all values are callables.
	 	 	 Assert::isNonEmptyMap($callable);
	 	 	 Assert::allIsCallable(array_values($callable));

	 	 	 $callables = $callable;
	 	 }

	 	 foreach($fields as $wf_key => $wf){ //for each with field key => value pair
	 	 	 if(is_int($wf_key)){ //if key is integer, it means value is the field name.
	 	 	 	 #split the field using dot as separator to see if nested.
		 	 	 $field_parts = explode(".", $wf);
		 	 	 #check if the first field is really an include field
		 	 	 $first_wf = array_shift($field_parts);
		 	 	 $field = $this->check_with($first_wf);
		 	 	 $this->sbuilder->add_include([
		 	 	 	'with'     => implode(".", $field_parts), 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$first_wf] ?? null
		 	 	 ]);
		 	 	 unset($callables[$first_wf]);
	 	 	 }else{ //if key is string, it means key is the field name
	 	 	 	 $field = $this->check_with($wf_key);
	 	 	 	 $this->sbuilder->add_include([
		 	 	 	'with'     => $wf, 
		 	 	 	'relation' => $field, 
		 	 	 	'tuning'   => $callables[$wf_key] ?? null
		 	 	 ]);
		 	 	 unset($callables[$wf_key]);
	 	 	 }
	 	 }

	 	 $this->sbuilder->withcallbacks = $callables;
	 	 return $this;
	 }
}
