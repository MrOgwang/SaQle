<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Field\Types\Base\{Simple, Relation};
use SaQle\Orm\Entities\Field\Types\{Pk, BooleanField, FileField, OneToOne, PhpTimestampField, DateField, DateTimeField, TimeField, TimestampField, VirtualField};

class TableInfo{
     private bool $remove_fields = false;

	 //Whether this is a temporary table or not
 	 public bool $temporary = false {
 	 	 set(bool $value){
 	 	 	 $this->temporary = $value;
 	 	 }

 	 	 get => $this->temporary;
 	 }

 	 //the table primary key name
 	 public private(set) string $pk_name = '' {
 	 	 set(string $value){
 	 	 	 $this->pk_name = $value;
 	 	 }

 	 	 get => $this->pk_name;
 	 }

 	 //the table primary key type
 	 public string $pk_type = PRIMARY_KEY_TYPE {
 	 	 set(string $value){
 	 	 	 $this->pk_type = $value;
 	 	 }

 	 	 get => $this->pk_type;
 	 }

 	 //the value of this property will represent the entire model
 	 public string $name_property = "" {
 	 	 set(string $value){
 	 	 	 $this->name_property = $value;
 	 	 }

 	 	 get => $this->name_property;
 	 }

 	 //the class name of the model
 	 public string $model_class = "" {
 	 	 set(string $value){
 	 	 	 $this->model_class = $value;
 	 	 }

 	 	 get => $this->model_class;
 	 }

 	 //the name of the database table associated with this model
 	 public string $db_table {
 	 	 set(string $value){
 	 	 	 $this->db_table = $value;
 	 	 }

 	 	 get => $this->db_table;
 	 }

 	 //the name of the database context class this model is defined
 	 public string $db_class {
 	 	 set(string $value){
 	 	 	 $this->db_class = $value;
 	 	 }

 	 	 get => $this->db_class;
 	 }

 	 //whether to automatically include created by and modified by fields in the model
 	 public bool $auto_cm = MODEL_AUTO_CM_FIELDS {
 	 	 set(bool $value){
 	 	 	 $this->auto_cm = $value;
             $this->toggle_cm_fields(switch: $value);
 	 	 }

 	 	 get => $this->auto_cm;
 	 }

 	 //whether to automatically incude created at and modified at fields in the model
 	 public bool $auto_cmdt = MODEL_AUTO_CMDT_FIELDS {
 	 	 set(bool $value){
 	 	 	 $this->auto_cmdt = $value;
             $this->toggle_cmdt_fields(switch: $value);
 	 	 }

 	 	 get => $this->auto_cmdt;
 	 }

 	 //whether to format php timestamps in auto cmdt fields to human readable format
 	 public bool $format_cmdt = true {
 	 	 set(bool $value){
 	 	 	 $this->format_cmdt = $value;
 	 	 }

 	 	 get => $this->format_cmdt;
 	 }

 	 //the type for auto cmdt
 	 public string $cmdt_type = DB_AUTO_CMDT_TYPE {
 	 	 set(string $value){
 	 	 	 $this->cmdt_type = $value;
 	 	 }

 	 	 get => $this->cmdt_type;
 	 }

 	 //whether to automatically include a deleted field in the model
 	 public bool $soft_delete = MODEL_SOFT_DELETE {
 	 	 set(bool $value){
 	 	 	 $this->soft_delete = $value;
             $this->toggle_delete_fields(switch: $value);
 	 	 }

 	 	 get => $this->soft_delete;
 	 }

 	 //an array of all the names of the navigation fields
 	 public array $nav_field_names = [] {
 	 	 set(array $value){
 	 	 	 $this->nav_field_names = $value;
 	 	 }

 	 	 get => $this->nav_field_names;
 	 }

 	 //an array of the names of the foreign key fields
 	 public array $fk_field_names = [] {
 	 	 set(array $value){
 	 	 	 $this->fk_field_names = $value;
 	 	 }

 	 	 get => $this->fk_field_names;
 	 }

 	 //all the table column names as they have been defined in the model, inluding navigation column names
 	 public private(set) array $column_names = [] {
 	 	 set(array $value){
 	 	 	 $this->column_names = $value;
 	 	 }

 	 	 get => $this->column_names;
 	 }

 	 //all the table column names as they have been defined in the model, excluding navigation column names
 	 public private(set) array $actual_column_names = [] {
 	 	 set(array $value){
 	 	 	 $this->actual_column_names = $value;
 	 	 }

 	 	 get => $this->actual_column_names;
 	 }

 	 //the name of the created at field
 	 public string $created_at_field = MODEL_CREATED_AT_FIELD {
 	 	 set(string $value){
 	 	 	 $this->created_at_field = $value;
 	 	 }

 	 	 get => $this->created_at_field;
 	 }

 	 //the name of the created by field
 	 public string $created_by_field = MODEL_CREATED_BY_FIELD {
 	 	 set(string $value){
 	 	 	 $this->created_by_field = $value;
 	 	 }

 	 	 get => $this->created_by_field;
 	 }

 	 //the name of the modified at field
 	 public string $modified_at_field = MODEL_MODIFIED_AT_FIELD {
 	 	 set(string $value){
 	 	 	 $this->modified_at_field = $value;
 	 	 }

 	 	 get => $this->modified_at_field;
 	 }

 	 //the name of the modified by field
 	 public string $modified_by_field = MODEL_MODIFIED_BY_FIELD {
 	 	 set(string $value){
 	 	 	 $this->modified_by_field = $value;
 	 	 }

 	 	 get => $this->modified_by_field;
 	 }

 	 //whether to enable multitenancy for this model or not
 	 public bool $enable_multitenancy = ENABLE_MULTITENANCY {
 	 	 set(bool $value){
 	 	 	 $this->enable_multitenancy = $value;
             $this->toggle_tenant_field(switch: $value);
 	 	 }

 	 	 get => $this->enable_multitenancy;
 	 }

 	 //the name of the deleted field
 	 public string $deleted_field = MODEL_DELETED_FIELD {
 	 	 set(string $value){
 	 	 	 $this->deleted_field = $value;
 	 	 }

 	 	 get => $this->deleted_field;
 	 }

 	 //the name of the deleted at field
 	 public string $deleted_at_field = MODEL_DELETED_AT_FIELD {
 	 	 set(string $value){
 	 	 	 $this->deleted_at_field = $value;
 	 	 }

 	 	 get => $this->deleted_at_field;
 	 }

 	 //the name of the deleted by field
 	 public string $deleted_by_field = MODEL_DELETED_BY_FIELD {
 	 	 set(string $value){
 	 	 	 $this->deleted_by_field = $value;
 	 	 }

 	 	 get => $this->deleted_by_field;
 	 }

 	 //whether to auto initialize database timestamps
 	 public bool $db_auto_init_timestamp = DB_AUTO_INIT_TIMESTAMP {
 	 	 set(bool $value){
 	 	 	 $this->db_auto_init_timestamp = $value;
 	 	 }

 	 	 get => $this->db_auto_init_timestamp;
 	 }

 	 //whether to auto update database timestamps
 	 public bool $db_auto_update_timestamp = DB_AUTO_UPDATE_TIMESTAMP {
 	 	 set(bool $value){
 	 	 	 $this->db_auto_update_timestamp = $value;
 	 	 }

 	 	 get => $this->db_auto_update_timestamp;
 	 }

 	 /**
      * Tell the model the action to take when an attempt to insert
      * duplicate data is made.
      * 
      * Options include: 
      * ABORT_WITH_ERROR - Abort the insert or update operation and throw an exception
      * 
      * INSERT_MINUS_DUPLICATE - Insert only the data that is not duplicating
      * 
      * UPDATE_ON_DUPLICATE - Update the record that is already existing with incoming values and return the updated version.
      * 
      * RETURN_EXISTING - Return existing record(s) as it is. (alongside newly added ones if multiple records are being inserted)
      * 
      * Defaults to the value set using MODEL_ACTION_ON_DUPLICATE constant in app config, which defaults to ABORT_WITH_ERROR
      * 
      * */
 	 public string $action_on_duplicate = MODEL_ACTION_ON_DUPLICATE {
 	 	 set(string $value){
             //ensure action is among the valid options
             $actions = ['ABORT_WITH_ERROR', 'INSERT_MINUS_DUPLICATE', 'UPDATE_ON_DUPLICATE', 'RETURN_EXISTING'];
             if(!in_array($value, $actions)){
                 throw new \Exception('The duplicate action provided is not valid. Valid duplicate actions are: '.implode(',', $actions));
             }
 	 	 	 $this->action_on_duplicate = $value;
 	 	 }

 	 	 get => $this->action_on_duplicate;
 	 }

 	 /**
      * Avoid duplicate data in your table by setting the unique fields,
      * it defaults to an empty array to indicate that rows in this table can contain duplicate
      * data.
      * */
     public array $unique_fields = [] {
 	 	 set(array $value){
             //confirm that all the fields provided exists.
             foreach($value as $v){
                 if(!array_key_exists($v, $this->column_names) && !array_key_exists($v, array_flip($this->column_names))){
                     throw new \Exception($v." is listed as a unique field but is not defined on this model!");
                 }
             }

             //store unique fields using db column names instead of field names
             $unique_columns = [];
             foreach($value as $v){
                 $unique_columns[] = $this->column_names[$v] ?? $v;
             }

 	 	 	 $this->unique_fields = $unique_columns;
 	 	 }

 	 	 get => $this->unique_fields;
 	 }


 	 /**
      * When you have provided more than one unique field,
      * tell the model whether you want to have them be unique together
      * or unique individually. Defaults to false
      * */
 	 public bool $unique_together = false {
 	 	 set(bool $value){
 	 	 	 $this->unique_together = $value;
 	 	 }

 	 	 get => $this->unique_together;
 	 }

 	 //an array of the fields defined in the model
 	 public array $fields = [] {
 	 	 set(array $value){

 	 	 	 //make sure $value is an non empty associative array
 	 	 	 Assert::isMap($value, 'Fields must be an associative array where the keys are field names and the values are field instances');
             
             $column_names = [];
             $actual_column_names = [];
             $file_field_names = [];
             $nav_field_names = [];
             $fk_field_names = [];
             $file_required_fields = [];
             $virtual_field_names = [];

 	 	 	 foreach($value as $n => $v){
 	 	 	 	 //make sure each element is a Field instance
 	 	 	 	 Assert::isInstanceOf($v, Simple::class, $n.' is not a field instance!');

                 $v->field_name = $n;

 	 	 	 	 $column_names[$n] = $v->column_name;

                 $navigation = false;
                 $virtual = false;
 	 	 	 	 if($v instanceof FileField){
 	 	 	 	 	 $file_field_names[] = $n;
 	 	 	 	 	 $file_required_fields[$n] = $v->required_fields;
 	 	 	 	 }elseif($v instanceof Relation){
 	 	 	 	 	 $v->pmodel = $this->model_class;
			 	 	 if($v->navigation){
			 	 	 	 $nav_field_names[] = $n;
			 	 	 	 $navigation = true;
			 	 	 }else{
			 	 	 	 $fk_field_names[] = $n;
			 	 	 }
			 	 }elseif($v instanceof Pk){
		 	 	     $this->pk_name = $n;
		 	     }elseif($v instanceof VirtualField){
                     $virtual_field_names[] = $n;
                     $virtual = true;
                 }

 	 	 	 	 if(!$navigation && !$virtual){
 	 	 	 	 	 $actual_column_names[$n] = $v->column_name;
 	 	 	 	 }
 	 	 	 }

 	 	 	 $this->fields = !$this->remove_fields ? array_merge($this->fields, $value) : $value;
 	 	 	 $this->column_names = !$this->remove_fields ? array_merge($this->column_names, $column_names) : $column_names;
 	 	 	 $this->actual_column_names = !$this->remove_fields ? array_merge($this->actual_column_names, $actual_column_names) : $actual_column_names;
             $this->file_field_names = !$this->remove_fields ? array_merge($this->file_field_names, $file_field_names) : $file_field_names;
             $this->file_required_fields = !$this->remove_fields ? array_merge($this->file_required_fields, $file_required_fields) : $file_required_fields;
             $this->nav_field_names = !$this->remove_fields ? array_merge($this->nav_field_names, $nav_field_names) : $nav_field_names;
 	 	 	 $this->fk_field_names = !$this->remove_fields ? array_merge($this->fk_field_names, $fk_field_names) : $fk_field_names;
             $this->virtual_field_names = $virtual_field_names;
             $this->defined_field_names = array_diff(array_keys($this->fields), $this->non_defined_field_names, $virtual_field_names);
 	 	 }

 	 	 get => $this->fields;
 	 }

 	 //an array of the names of the fields defined in the model
 	 public array $defined_field_names = [] {
 	 	 set(array $value){
 	 	 	 $this->defined_field_names = $value;
 	 	 }

 	 	 get => $this->defined_field_names;
 	 }

     //an array of the names of the fields not defined in the model
     public array $non_defined_field_names = [] {
         set(array $value){
             $this->non_defined_field_names = $value;
         }

         get => $this->non_defined_field_names;
     }

     //an array of the names of all the virtual fields
     public array $virtual_field_names = [] {
         set(array $value){
             $this->virtual_field_names = $value;
         }

         get => $this->virtual_field_names;
     }

 	 //an array of the names of the file fields defined in the model
 	 public private(set) array $file_field_names = [] {
 	 	 set(array $value){
 	 	 	 $this->file_field_names = $value;
 	 	 }

 	 	 get => $this->file_field_names;
 	 }

 	 /**
 	  * an array of all the field names that must be included in select clause 
 	  * to support file fields callback path, rename, url and default_url functions
 	  * */
 	 public private(set) array $file_required_fields = [] {
 	 	 set(array $value){
 	 	 	 $this->file_required_fields = $value;
 	 	 }

 	 	 get => $this->file_required_fields;
 	 }

     //add or remove the tenant field depending on the multitenancy setting
     private function toggle_tenant_field(bool $switch = true, string $signal_type = 'pre') : void{
         $auto_fields = [];
         if($switch && TENANT_MODEL_CLASS){
             $auto_fields['tenant'] = new OneToOne(required: false, fmodel: TENANT_MODEL_CLASS, field: 'tenant', column_name: 'tenant_id');
             $this->update_field_names($auto_fields, 'add', 'tenant');
             return;
         }

         $auto_fields['tenant'] = true;
         $this->update_field_names($auto_fields, 'remove', 'tenant');
         return;
     }

     //add or remove creator and modifier fields depending on auto_cm setting
     private function toggle_cm_fields(bool $switch = true, string $signal_type = 'pre') : void{
         $auto_fields = [];
         if( $switch && AUTH_MODEL_CLASS ){
             $auto_fields['author'] = new OneToOne(required: false, fmodel: AUTH_MODEL_CLASS, field: 'author', fk: 'user_id', column_name: $this->created_by_field);
             $auto_fields['modifier'] = new OneToOne(required: false, fmodel: AUTH_MODEL_CLASS, field: 'modifier', fk: 'user_id', column_name: $this->modified_by_field);
             $this->update_field_names($auto_fields, 'add', 'cm');
             return;
         }

         $auto_fields['author'] = true;
         $auto_fields['modifier'] = true;
         $this->update_field_names($auto_fields, 'remove', 'cm');
         return;
     }

     //add or remove created at and modified at date time stamps depending on auto_cmdt setting
     private function toggle_cmdt_fields(bool $switch = true, string $signal_type = 'pre') : void{
         $auto_fields = [];
         if( $switch ){
             $auto_fields[$this->created_at_field] = match($this->cmdt_type){
                'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
                'DATE'         => new DateField(required: false, strict: false),
                'DATETIME'     => new DateTimeField(required: false, strict: false),
                'TIME'         => new TimeField(required: false, strict: false),
                'TIMESTAMP'    => new TimestampField(required: false, strict: false)
             };
             $auto_fields[$this->modified_at_field] = match($this->cmdt_type){
                'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
                'DATE'         => new DateField(required: false, strict: false),
                'DATETIME'     => new DateTimeField(required: false, strict: false),
                'TIME'         => new TimeField(required: false, strict: false),
                'TIMESTAMP'    => new TimestampField(required: false, strict: false)
             };
             $this->update_field_names($auto_fields, 'add', 'cmdt');
             return;
         }

         $auto_fields[$this->created_at_field] = true;
         $auto_fields[$this->modified_at_field] = true;
         $this->update_field_names($auto_fields, 'remove', 'cmdt');
         return;
     }

     //add or remove soft delete fields depending on soft_delete setting
     private function toggle_delete_fields(bool $switch = true, string $signal_type = 'pre') : void{
         $auto_fields = [];
         if( $switch ){
             $auto_fields[$this->deleted_field] = new BooleanField(required: false, zero: true, absolute: true);
             if(AUTH_MODEL_CLASS){
                 $auto_fields['remover'] = new OneToOne(required: false, fmodel: AUTH_MODEL_CLASS, field: 'remover', fk: 'user_id', column_name: $this->deleted_by_field);
             }
             $auto_fields[$this->deleted_at_field] = match($this->cmdt_type){
                'PHPTIMESTAMP' => new PhpTimestampField(required: false, zero: false, absolute: true),
                'DATE'         => new DateField(required: false, strict: false),
                'DATETIME'     => new DateTimeField(required: false, strict: false),
                'TIME'         => new TimeField(required: false, strict: false),
                'TIMESTAMP'    => new TimestampField(required: false, strict: false)
             };
             $this->update_field_names($auto_fields, 'add', 'delete');
             return;
         }

         $auto_fields[$this->deleted_field] = true;
         $auto_fields[$this->deleted_at_field] = true;
         $auto_fields['remover'] = true;
         $this->update_field_names($auto_fields, 'remove', 'delete');
         return;
     }

     private function update_field_names(array $fields = [], string $option = 'add', string $where = ''){
         if($option === 'add'){
             $this->non_defined_field_names = array_unique(array_merge($this->non_defined_field_names, array_keys($fields)));
             $this->remove_fields           = false;
             $this->fields                  = $fields;
         }else{
             $this->non_defined_field_names = array_unique(array_diff($this->non_defined_field_names, array_keys($fields)));
             $new_field_names = array_unique(array_diff(array_keys($this->fields), array_keys($fields)));
             $to_remain_fields = [];
             foreach($new_field_names as $f){
                 $to_remain_fields[$f] = $this->fields[$f];
             }

             $this->remove_fields = true;
             $this->fields        = $to_remain_fields;
         }
     }

     public function __construct(){
         $this->toggle_tenant_field(switch: ENABLE_MULTITENANCY);
         $this->toggle_cm_fields(switch: MODEL_AUTO_CM_FIELDS);
         $this->toggle_cmdt_fields(switch: MODEL_AUTO_CMDT_FIELDS);
         $this->toggle_delete_fields(switch: MODEL_SOFT_DELETE);
     }
}
?>