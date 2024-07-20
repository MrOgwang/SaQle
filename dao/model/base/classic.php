<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\Interfaces\IValidator;
use SaQle\Dao\Field\Relations\Interfaces\IRelation;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

abstract class Dao implements IModel{
	 /**
	  * Current request. This must be removed in future.
	  * */
	 protected Request $request;

	 /**
	 	 * This is key => val array of data access object meta data with the following optional keys
	 	 * @var string name_property : the property from which a value object deroves its name
	 	 * @var bool   auto_cm_fields: This tells the model to include author and modifier fields
	 	 * @var bool   auto_cmdt_fields: This tells the nodel to include the datetime(created, modified) fields
	 	 * @var bool   soft_delete: This tells the model to include a deleted field to enable soft delets
	 	 * @var string dbtable: This is the name of the database table associated with model.
	 	 * @var string dbtable: This is the name of the database table associated with model.
	 	 * */
	 private array $meta = [];
	 public function __construct(...$kwargs){
	 	 $nameparts = explode($this::class);
	 	 $lastpart  = end($nameparts);
	 	 $this->meta['dbtable'] = strtolower($lastpart)."s";
	 }
     
     /**
      * Set the request property for dao
      * @param Request request
      * */
	 public function set_request(Request $request){
	 	$this->request = $request;
	 }

     /**
      * Set dao meta data as explained above
      * */
	 public function set_meta(array $meta){
     	$this->meta = array_merge($meta, $this->meta);
     }

     /**
      * Get the request
      * */
	 public function get_request(){
	 	return $this->request;
	 }

     /**
      * Get dao meta data
      * */
	 public function meta(){
	 	return $this->meta;
	 }


	 //utilities

     /**
      * Return the dao name property as set in the meta data
      * */
	 public function get_name_property(){
	 	 $meta = $this->meta();
	 	 return array_key_exists("name_property", $meta) ? $meta['name_property'] : "";
	 }

	 /**
      * Return the name of the database table associated with model
      * */
	 public function get_db_table(){
	 	 return $this->meta['dbtable'];
	 }

	 /**
	  * Return the auto_cm_fields property as set in the meta data
	  * */
	 public function get_auto_cm(){
	 	 return array_key_exists("auto_cm_fields", $this->meta) ? $meta['auto_cm_fields'] : false;
	 }

	 /**
	  * Return the auto_cmdt_fields property as set in the meta data
	  * */
	 public function get_auto_cmdt(){
	 	 return array_key_exists("auto_cmdt_fields", $this->meta) ? $meta['auto_cmdt_fields'] : false;
	 }

	 /**
	  * Return the soft_delete property as set in the meta data
	  * */
	 public function get_soft_delete(){
	 	 return array_key_exists("soft_delete", $this->meta) ? $meta['soft_delete'] : false;
	 }


	 public static function db(){
	 	 $db_class = false;
	 	 $table_name = null;
	 	 $context_classes = array_keys(DB_CONTEXT_CLASSES);
	 	 $current_model_name = get_called_class();
	 	 for($x = 0; $x < count($context_classes); $x++){
	 	 	$context_class_name = $context_classes[$x];
	 	 	$models = $context_class_name::get_models();
	 	 	$model_classes = array_values($models);
	 	 	if(in_array($current_model_name, $model_classes)){
	 	 		$db_class = $context_class_name;
	 	 		$table_name = array_keys($models)[array_search($current_model_name, $model_classes)];
	 	 		break;
	 	 	}
	 	 }

	 	 if(!$db_class || !$table_name){
	 	 	throw new \Exception($current_model_name.": Model not registered with any db contexts!");
	 	 }

	 	 $dbcontext = Cf::create(ContainerService::class)->createDbContext($db_class);
         return $dbcontext->get($table_name);
	 }
}
?>