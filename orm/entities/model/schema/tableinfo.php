<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\{Pk, BooleanField, FileField, OneToOne, PhpTimestampField, DateField, DateTimeField, TimeField, TimestampField, VirtualField};

class TableInfo{
     
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
     public string $pk_type = '' {
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
     public bool $with_user_audit = false {
         set(bool $value){
             $this->with_user_audit = $value;
         }

         get => $this->with_user_audit;
     }

     //whether to automatically incude created at and modified at fields in the model
     public bool $with_timestamps = false {
         set(bool $value){
             $this->with_timestamps = $value;
         }

         get => $this->with_timestamps;
     }

     //whether to format php timestamps in auto cmdt fields to human readable format
     public bool $format_cmdt = false {
         set(bool $value){
             $this->format_cmdt = $value;
         }

         get => $this->format_cmdt;
     }

     //whether to automatically include a deleted field in the model
     public bool $with_soft_delete = false {
         set(bool $value){
             $this->with_soft_delete = $value;
         }

         get => $this->with_soft_delete;
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

     //all the table column names as they have been defined in the model, excluding navigation and virtual column names
     public private(set) array $actual_column_names = [] {
         set(array $value){
             $this->actual_column_names = $value;
         }

         get => $this->actual_column_names;
     }

     //the name of the created at field
     public string $created_at_field = '' {
         set(string $value){
             $this->created_at_field = $value;
         }

         get => $this->created_at_field;
     }

     //the name of the created by field
     public string $created_by_field = '' {
         set(string $value){
             $this->created_by_field = $value;
         }

         get => $this->created_by_field;
     }

     //the name of the modified at field
     public string $modified_at_field = '' {
         set(string $value){
             $this->modified_at_field = $value;
         }

         get => $this->modified_at_field;
     }

     //the name of the modified by field
     public string $modified_by_field = '' {
         set(string $value){
             $this->modified_by_field = $value;
         }

         get => $this->modified_by_field;
     }

     //whether to enable multitenancy for this model or not
     public bool $with_multitenancy = false {
         set(bool $value){
             $this->with_multitenancy = $value;
         }

         get => $this->with_multitenancy;
     }

     //the name of the deleted field
     public string $deleted_field = '' {
         set(string $value){
             $this->deleted_field = $value;
         }

         get => $this->deleted_field;
     }

     //the name of the deleted at field
     public string $deleted_at_field = '' {
         set(string $value){
             $this->deleted_at_field = $value;
         }

         get => $this->deleted_at_field;
     }

     //the name of the deleted by field
     public string $deleted_by_field = '' {
         set(string $value){
             $this->deleted_by_field = $value;
         }

         get => $this->deleted_by_field;
     }

     //the name of the deleted by field
     public string $tenant_field = '' {
         set(string $value){
             $this->tenant_field = $value;
         }

         get => $this->tenant_field;
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
      * Defaults to the value set using config('action_on_duplicate') constant in app config, which defaults to ABORT_WITH_ERROR
      * 
      * */
     public string $action_on_duplicate = '' {
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
     public array $fields = []{
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
                 Assert::isInstanceOf($v, IField::class, $n.' is not a field instance!');

                 //resolve the primary key
                 if($v instanceof Pk){
                     $v = $v->resolve();
                     $this->pk_name = $n;
                 }

                 $v->name($n);

                 $column_names[$n] = $v->get_column();

                 $navigation = false;
                 $virtual = false;
                 if($v instanceof FileField){
                     $file_field_names[] = $n;
                     $file_required_fields[$n] = $v->get_depends_on();
                 }elseif($v instanceof Relation){
                     $v->local_model($this->model_class);
                     if($v->is_navigation()){
                         $nav_field_names[] = $n;
                         $navigation = true;
                     }else{
                         $fk_field_names[] = $n;
                     }
                 }elseif($v instanceof VirtualField){
                     $virtual_field_names[] = $n;
                     $virtual = true;
                 }

                 if(!$navigation && !$virtual){
                     $actual_column_names[$n] = $v->get_column();
                 }
             }

             $this->fields = $value;
             $this->column_names = $column_names;
             $this->actual_column_names = $actual_column_names;
             $this->file_field_names = $file_field_names;
             $this->file_required_fields = $file_required_fields;
             $this->nav_field_names = $nav_field_names;
             $this->fk_field_names = $fk_field_names;
             $this->virtual_field_names = $virtual_field_names;
             $this->defined_field_names = array_diff(array_keys($this->fields), $this->audit_field_names, $virtual_field_names);
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
     public array $audit_field_names = [] {
         set(array $value){
             $this->audit_field_names = $value;
         }

         get => $this->audit_field_names;
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
      * to support file fields callback path, rename, url and default_path functions
      * */
     public private(set) array $file_required_fields = [] {
         set(array $value){
             $this->file_required_fields = $value;
         }

         get => $this->file_required_fields;
     }

     /**
      * this is a property used to override the auto added fields from with_user_audit, with_timestamps and with_soft_delete settings.
      * Instead of the default field types provided, provide a field name with a different field type
      * to override the default ones.
      *
      * */
     public array $audit_fields_override = []{
         set(array $value){
             $this->audit_fields_override = $value;
         }

         get => $this->audit_fields_override;
     }

     private function assert_model_exists(string $model_class){
         if($model_class && class_exists($model_class) && is_subclass_of($model_class, Model::class)){
             return true;
         }

         return false;
     }

     //add or remove the tenant field depending on the multitenancy setting
     public function get_tenant_fields() : array {
         if($this->with_multitenancy){
             $tenant_model_class = config('tenant_model_class');
             if($this->assert_model_exists($tenant_model_class)){
                 return [
                     $this->tenant_field => $this->audit_fields_override[$this->tenant_field] ?? new OneToOne(related_model: $tenant_model_class)
                 ];
             }
         }

         return [];
     }

     //add or remove creator and modifier fields depending on with_user_audit setting
     private function get_user_audit_fields(bool $switch = true) : array {
         if($this->with_user_audit){
             $auth_model_class = config('auth_model_class');
             if($this->assert_model_exists($auth_model_class)){
                 return [
                     $this->created_by_field => $this->audit_fields_override[$this->created_by_field] ??  
                     new OneToOne(related_model: $auth_model_class, foreign_key: $auth_model_class::get_pk_name()),

                     $this->modified_by_field => $this->audit_fields_override[$this->modified_by_field] ?? 
                     new OneToOne(related_model: $auth_model_class, foreign_key: $auth_model_class::get_pk_name())
                 ];
             }
         }

         return [];
     }

     //add or remove created at and modified at date time stamps depending on with_timestamps setting
     private function get_timestamp_fields(bool $switch = true) : array {
         if($this->with_timestamps){
             return [
                 $this->created_at_field => $this->audit_fields_override[$this->created_at_field] ?? new DateTimeField(),
                 $this->modified_at_field => $this->audit_fields_override[$this->modified_at_field] ?? new DateTimeField()
             ];
         }

         return [];
     }

     //add or remove soft delete fields depending on with_soft_delete setting
     private function get_delete_fields(bool $switch = true) : array {
         if($this->with_soft_delete){
             $fields = [
                 $this->deleted_field => $this->audit_fields_override[$this->deleted_field] ?? new BooleanField(),
                 $this->deleted_at_field => $this->audit_fields_override[$this->deleted_at_field] ?? new DateTimeField()
             ];

             $auth_model_class = config('auth_model_class');
             if($this->assert_model_exists($auth_model_class)){
                $fields[$this->deleted_by_field] = $this->audit_fields_override[$this->deleted_by_field] ?? 
                new OneToOne(related_model: $auth_model_class, foreign_key: $auth_model_class::get_pk_name());
             }

             return $fields;
         }

         return [];
     }

     public function add_audit_fields(){
         $audit_fields = array_merge(
             $this->get_tenant_fields(),
             $this->get_user_audit_fields(),
             $this->get_timestamp_fields(),
             $this->get_delete_fields()
         );

         $this->audit_field_names = array_keys($audit_fields);

         $this->fields = array_merge($this->fields, $audit_fields);
     }

     public function initialize_model_meta($table_name, $database_context, $model_class){
         $this->db_table = $table_name;
         $this->db_class = $database_context;
         $this->model_class = $model_class;
         $this->pk_type = config('primary_key_type');
         $this->with_user_audit = config('with_user_audit');
         $this->with_timestamps = config('with_timestamps');
         $this->with_soft_delete = config('with_soft_delete');
         $this->created_at_field = config('created_at_field');
         $this->created_by_field = config('created_by_field');
         $this->modified_at_field = config('modified_at_field');
         $this->modified_by_field = config('modified_by_field');
         $this->with_multitenancy = config('with_multitenancy');
         $this->tenant_field = config('tenant_field');
         $this->deleted_field = config('deleted_field');
         $this->deleted_at_field = config('deleted_at_field');
         $this->deleted_by_field = config('deleted_by_field');
         $this->action_on_duplicate = config('action_on_duplicate');
     }

}
