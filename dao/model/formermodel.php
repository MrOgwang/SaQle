<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Model\Interfaces\IModel;
use SaQle\Commons\StringUtils;
use SaQle\Http\Request\Request;
use SaQle\Services\Container\Cf;
use SaQle\Services\Container\ContainerService;
use SaQle\Dao\Model\Interfaces\ModelCollection;
use SaQle\Dao\Field\Relations\{One2One, One2Many, Many2Many};

#[\AllowDynamicProperties]
abstract class Model implements IModel{
	 use StringUtils;
	 private $data_state = [];
	 public function __construct(...$kwargs){
	 	 $this->set_values(...$kwargs);
	 	 $this->data_state = $this->get_data_state();
	 }

	 public function set_values(...$kwargs){
	 	 $reflect = new \ReflectionClass($this);
	 	
	 	 $schema = $this->get_schema();
	 	 $db_columns = $schema->meta->column_names;
	 	 $db_columns_flip = array_flip($db_columns);

	 	 foreach($kwargs as $k => $v){
	 	 	 $field_name = null;
	 	 	 $col_name   = null;
	 	 	 if(array_key_exists($k, $db_columns)){
	 	 	 	 $field_name = $k;
	 	 	 	 $col_name = $db_columns[$k];
	 	 	 }

	 	 	 if(!$field_name && !$col_name){
	 	 	 	 if(array_key_exists($k, $db_columns_flip)){
	 	 	 	     $col_name = $k;
	 	 	 	     $field_name = $db_columns_flip[$k];
	 	 	     }
	 	 	 }

	 	 	 if(!$field_name && !$col_name){
	 	 	 	 //create a new dynamic field.
	 	 	 	 $this->$k = $v;
	 	 	 	 continue;
	 	 	 }

	 	 	 $this->$field_name = $v;
	 	 }
	 }

     private function classify_fields(){
     	 $schema = $this->get_schema();
	 	 $defined_field_names = $schema->meta->defined_field_names;
	 	 $nk_field_names = $schema->meta->nav_field_names;
	 	 $fk_field_names = $schema->meta->fk_field_names;

	 	 $simple_fields = array_diff($defined_field_names, array_merge($nk_field_names, $fk_field_names));
	 	 $nk_fields = array_diff($defined_field_names, array_merge($simple_fields, $fk_field_names));
	 	 $fk_fields = array_diff($defined_field_names, array_merge($simple_fields, $nk_field_names));
	 	 return [$defined_field_names, $simple_fields, $fk_fields, $nk_fields];
     }

	 private function get_data_state(){
	 	 $data_state = [];
	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();

	 	 $ref = new \ReflectionClass($this);
         $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
         $schema = $this->get_schema();
	     foreach($properties as $property){
	     	 $property_name = $property->getName();
			 $property_value = $property->getValue($this);
	     	 if(in_array($property_name, $simple_fields)){
	     	 	 $data_state[$property_name] = $property_value;
	     	 }elseif(in_array($property_name, $fk_fields) || in_array($property_name, $nk_fields)){
	     	 	 
	     	 	 if($property_value instanceof IModel){
	     	 	 	 $relation = $schema->is_include($property_name);
	     	 	     $fk_name = $relation->fk;
	     	 	     $pk_values = $property_value->get_field_value($fk_name);
	     	 	     $data_state[$property_name] = $pk_values;
	     	 	     if(in_array($property_name, $nk_fields) && $relation instanceof Many2Many){
	     	 	     	 [$table_name, $schema, $ctx] = $relation->get_through_model_schema();
	     	 	     	 $data_state[$property_name] = ['key' => $fk_name, 'values' => $pk_values, 'table' => $table_name, 'schema' => $schema, 'context' => $ctx];
	     	 	     }
	     	 	 }else{
	     	 	 	 $data_state[$property_name] = $property_value;
	     	 	 }
	     	 }
	     }
	     return $data_state;
	 }

	 /**
	  * Get the schema associated with model.
	  * */
	 public static abstract function get_schema();

	 public function get_field_value($name){
	 	 return $this->$name;
	 }

	 public static function db2($table_name, $model_class, $db_class, $table_aliase = null, $table_ref = null){
	 	 $manager = Cf::create(ContainerService::class)->createContextModelManager($db_class);
         $manager->initialize(
         	table_name:      $table_name, 
         	dbcontext_class: $db_class, 
         	model_class:     $model_class,
         	table_aliase:    $table_aliase,
         	table_ref:       $table_ref
         );
         return $manager;
	 }

	 public static function db($table_aliase = null, $table_ref = null){
	 	 $called_class = get_called_class();
	 	 [$db_class, $table_name] = $called_class::get_schema()->get_table_n_dbcontext();
	 	 
	 	 $manager = Cf::create(ContainerService::class)->createContextModelManager($db_class);
         $manager->initialize(
             table_name:      $table_name, 
         	 dbcontext_class: $db_class, 
         	 table_aliase:    $table_aliase,
         	 table_ref:       $table_ref
         );
         return $manager;
	 }

	 public function get_state_change($new_data_state = null, $update_optional = null){
	 	 $simple_changed = [];
	 	 $fk_changed = [];
	 	 $nk_changed = [];
	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();
	 	 $new_data_state = $new_data_state ?? $this->get_data_state();

	 	 foreach($new_data_state as $key => $val){
	 	 	 
	 	 	 if($val != $this->data_state[$key] && ( !is_null($val) && $val != '')){ //This null and empty string condition must be rechecked!
	 	 	 	 if(in_array($key, $simple_fields)){
		 	 	 	 $simple_changed[$key] = $val;
		 	 	 }elseif(in_array($key, $fk_fields)){
		 	 	 	 $fk_changed[$key] = $val;
		 	 	 }elseif(in_array($key, $nk_fields)){
		 	 	 	 /**
		 	 	 	  * I am assuming this is many to many navigation key, this part must be reworked
		 	 	 	  * to accomodate many to one and one to one cases as well
		 	 	 	  * */
		 	 	 	 $current_values = $val['values'];
		 	 	 	 $former_values = $this->data_state[$key]['values'];

		 	 	 	 $added_values = array_diff($current_values, $former_values);
		 	 	 	 $removed_values = array_diff($former_values, $current_values);

		 	 	 	 $val['added'] = $added_values;
		 	 	 	 $val['removed'] = $removed_values;

		 	 	 	 $nk_changed[$key] = $val;
		 	 	 }

	 	 	 }
	 	 }
	 	 //echo "\n\n";

	 	 return [$simple_changed, $fk_changed, $nk_changed];
	 }

	 /**
	  * This save assumes the data was set via the model instructor.
	  * Notes on save:
	  * 1. By default only simple values are saved or updated when save is called.
	  * 2. Relation values(OneToOne, OneToMany, ManyToMany) will only be saved when explicitly requested via 'with' interface
	  * 3. While new relation objects can be created, they cannot be updated from here. Update relation ojects via their own models.
	  * 4. Values for properties that were not explicitly defined i.e values generated by auto_cm_fields, auto_cmdt_fields, soft_delete and enable_multitenancy flags will be filled in automatically. If there are values assigned for these properties here, they will be ignored.
	  * */
	 public function save(?array $update_optional = null){
	 	 $current_data_state = $this->get_data_state();
	     
	 	 [$savable_simple, $savable_nk, $savable_fk, $pk_name] = $this->get_savable_values();
	 	 $with_fields = [];

	 	 #If there are foreign relations, save them first. FK relations are one to one, so the save is expected to return a single object.
	 	 $saved_fk_ids = [];
	 	 foreach($savable_fk as $fk_key => $fk_value){
	 	 	 $with_fields[] = $fk_key;
	 	 	 $s = $fk_value[0]->save();
	 	 	 if($s){
	 	 	 	 $fk_name = $fk_value[1]->fk;
	 	 	 	 $saved_fk_ids[$fk_key] = $s->get_field_value($fk_name);
	 	 	 }
	 	 }

	 	 #Save the current object next.
	 	 $object = null;
	 	 $object_data = array_combine(array_keys($savable_simple), array_column(array_values($savable_simple), 0));
	 	 if(isset($savable_simple[$pk_name][0])){ //this object has a value for primary key field.
	 	 	 
	 	 	 #acquire the object
	 	 	 $object = self::db()->with(array_merge(array_keys($savable_nk), array_keys($savable_fk)))
	 	 	 ->where($pk_name, $savable_simple[$pk_name][0])->tomodel(true)->first_or_default();

	 	 	 if($object){

	 	 	 	 #get the changes made
	 	 	 	 [$simple_changed, $fk_changed, $nk_changed] = $this->get_state_change($current_data_state, $update_optional);
	 	 	 	 $simple_changes = array_merge($simple_changed, $fk_changed);
	 	 	 	 if($simple_changes){
	 	 	 	 	 #attempt an update
		 	 	 	 self::db()->set_data_state($current_data_state)->where($pk_name, $savable_simple[$pk_name][0])
		 	 	 	 ->set($simple_changes)->tomodel(true)->update();
	 	 	 	 }
                 
	 	 	 	 #remove any many to many bondings that are no longer existing.
	 	 	 	 foreach($nk_changed as $nkf => $nkv){
	 	 	 	 	 if($nkv['removed']){
	 	 	 	 	 	 $tms = $nkv['schema'];
		 	 	         $tm = $tms;
                 	     $tm::db2($nkv['table'], $tms, $nkv['context'])->where($nkv['key']."__in", $nkv['removed'])->delete(permanently: true);
	 	 	 	 	 }
                 }
	 	 	 }
	 	 }
         
         #save object in db if its null upto this point
	 	 if(!$object)
	 	 	 $object = self::db()->set_data_state($current_data_state)->add(array_merge($object_data, $saved_fk_ids))->tomodel(true)->save();

	 	 #abort save operation if object its still null at this point.
	 	 if(!$object)
	 	  	 throw new \Exception("Save operation aborted! This object could not be saved.");
	 	 
	 	 #save navigation key data
	 	 $saved_nk_ids = [];
	 	 foreach($savable_nk as $nk_key => $nk_value){
	 	 	 if($nk_value[0]){
	 	 	 	 $with_fields[] = $nk_key;
		 	 	 $nk_pk_name = $nk_value[1]->pk;
		 	 	 $nk_fk_name = $nk_value[1]->fk;
		 	 	 $nk_pk_value = $object->get_field_value($nk_pk_name);

		 	 	 if($nk_value[0] instanceof ModelCollection){
		 	 	 	 $s = $nk_value[0]->save();
	 	 	 	 	 $saved_nk_ids[$nk_key] = [$nk_pk_value, $s->get_field_value($nk_fk_name), $nk_value[1]->get_through_model_schema(), $nk_pk_name, $nk_fk_name];
	 	 	 	 }else{
	 	 	 	 	 $nk_fk_name = $nk_value[1]->fk;
	 	 	 	 	 $nk_value[0]->$nk_fk_name = $nk_pk_value;
	 	 	 	 	 $s = $nk_value[0]->save();
	 	 	 	 }
	 	 	 }
	 	 }

	 	 #tie the navigation key data to object via through model
	 	 foreach($saved_nk_ids as $_nk => $_ids){
	 	 	 if(count($_ids[1]) > 0){
		 	 	 $through_model_schema = $_ids[2];
		 	 	 $through_model = $through_model_schema[1];
		 	 	 $data = [];
		 	 	 $pk_col_name = $_ids[3];
		 	 	 $fk_col_name = $_ids[4];
		 	 	 $pk_col_val = $_ids[0];
		 	 	 foreach($_ids[1] as $fk_col_val){
		 	 	 	 $data[] = [$pk_col_name => $pk_col_val, $fk_col_name => $fk_col_val];
		 	 	 }
		 	 	 $connected = $through_model::db2($through_model_schema[0], $through_model_schema[1], $through_model_schema[2])
		 	 	 ->add_multiple($data)->save();
		 	 }
	 	 }
	 	 $getmana = $this->db()->set_data_state($current_data_state)->where($pk_name, $object->get_field_value($pk_name));
	 	 if($with_fields){
	 	 	$getmana->with($with_fields);
	 	 }

	 	 return $getmana->tomodel(true)->first_or_default();
	 }

	 public function get_savable_values(){
	 	 $savable_simple = [];
	 	 $savable_fk = [];
	 	 $savable_nk = [];

	 	 [$defined_field_names, $simple_fields, $fk_fields, $nk_fields] = $this->classify_fields();

	 	 $ref = new \ReflectionClass($this);
         $properties = $ref->getProperties(\ReflectionProperty::IS_PUBLIC);
         $schema = $this->get_schema();
	     foreach($properties as $property){
	     	 $property_name = $property->getName();
	     	 /**
              * Only take initialized properties. aka, properties with values
              * - validation mechanism will determine whether all the properties required for a successful save are there or not, so don't worry about it here.
              * Also take only those properties that wer explicitly defined on the schema associated with this model.
              * */
			 $rp = new \ReflectionProperty($this::class, $property_name);
			 if( $rp->isInitialized($this) && in_array($property_name, $defined_field_names) ){
			 	 //$property_type = str_replace("?", "", $property->getType()); 
		     	 $property_value = $property->getValue($this);
		     	 if(in_array($property_name, $simple_fields)){
		     	 	 $savable_simple[$property_name] = [$property_value, false];
		     	 }elseif(in_array($property_name, $nk_fields)){
                     $savable_nk[$property_name] = [$property_value, $schema->is_include($property_name)];
		     	 }elseif(in_array($property_name, $fk_fields)){
                     $savable_fk[$property_name] = [$property_value, $schema->is_include($property_name)];
		     	 }
			 }
	     }

	     return [$savable_simple, $savable_nk, $savable_fk, $schema->meta->pk_name];
	 }

	 static public function get_collection_class(){
	 	 $model_dao_class      = get_called_class();
	 	 $nameparts            = explode("\\", $model_dao_class);
	 	 $model_dao_name       = array_pop($nameparts);
         $model_dao_namespace  = implode("\\", $nameparts);

         $collection_namespace = $model_dao_namespace."\\Collections";
         $collection_name      = $model_dao_name."Collection";
         return $collection_namespace."\\".$collection_name;
	 }
}
?>