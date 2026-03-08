<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\{
     Pk, VirtualField, VideoField, ImageField, UuidField, UrlField,
     TimeField, TextField, SlugField, PhoneField, PasswordField, JsonField,
     IpAddressField, IntegerField, FloatField, FileField, EmailField,
     DecimalField, DateTimeField, DateField, ChoiceField, CharField, 
     BooleanField, OneToOne, ManyRelation
};
use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use RuntimeException;

final class Table {
     
     //Whether this is a temporary table or not
     private bool $_temporary = false;

     //the table primary key name
     private string $pk_name = '';

     //the table primary key type
     private string $pk_type = '';

     //the value of this property will represent the entire model
     private string $_name_property = "";

     //the class name of the model
     private string $model_class = ""; 

     //the name of the database table associated with this model
     private ?string $table_name = null;

     //the connection name to use for this model
     private ?string $connection_name = null;

     //whether to automatically include created by and modified by fields in the model
     private bool $_with_user_audit = false;

     //whether to automatically incude created at and modified at fields in the model
     private bool $_with_timestamps = false;

     //whether to automatically include a deleted field in the model
     private bool $_with_soft_delete = false;

     //an array of all the names of the navigation fields
     private array $nav_field_names = [];

     //an array of the names of the foreign key fields
     private array $fk_field_names = [];

     /**
      * When declaring fields on the model, the field names may be different from
      * the column names to be used in the database. 
      * 
      * This array keeps a reference of all the field names and their respective column names
      * as key => value pairs, where the key is the field name and the value is the column name.
      * */
     private array $field_column_refs = [];

     /**
      * These are all the table column names as they will appear
      * in the actual database table.
      * 
      * Navigation and Virtual Field column names are not here!
      * */
     private array $table_column_names = [];

     //the name of the created at field
     private string $created_at_column = '';

     //the name of the created by field
     private string $author_column = '';

     //the name of the modified at field
     private string $modified_at_column = '';

     //the name of the modified by field
     private string $modifier_column = '';

     //the name of the deleted field
     private string $is_removed_column = '';

     //the name of the deleted at field
     private string $removed_at_column = '';

     //the name of the deleted by field
     private string $remover_column = '';

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
      * Defaults to the value set using config('model.action_on_duplicate') constant in app config, which defaults to ABORT_WITH_ERROR
      * 
      * */
     private string $_action_on_duplicate = '';

     /**
      * Avoid duplicate data in your table by setting the unique fields,
      * it defaults to an empty array to indicate that rows in this table can contain duplicate
      * data.
      * */
     private array $unique_field_names = [];

     private array $_unique_fields = [];

     /**
      * The final model fields after state validation happens.
      * 
      * Note: Model state validation is not the same as model data validation. State validation
      * ensures that all fields defined for a model are correctly defined!
      * */
     private array $clean_fields = [] {
         set(array $value){
             foreach($value as $n => $v){
                 $has_table_column = true;
                 $this->field_column_refs[$n] = $v->get_column();

                 if($v instanceof FileField){
                     $this->file_field_names[] = $n;
                     $this->file_required_fields[$n] = $v->get_depends_on();
                 }elseif($v instanceof RelationField){
                     if($v->is_navigation()){
                         $this->nav_field_names[] = $n;
                         $has_table_column = false;
                     }else{
                         $this->fk_field_names[] = $n;
                     }
                 }elseif($v instanceof VirtualField){
                     $this->virtual_field_names[] = $n;
                     $has_table_column = false;
                 }

                 if($has_table_column){
                     $this->table_column_names[$n] = $v->get_column();
                     if($v->is_unique()){
                         $this->unique_field_names[] = $n;
                     }
                 }
             }

             $this->defined_field_names = array_diff(array_keys($value), $this->audit_field_names, $this->virtual_field_names);

             $this->clean_fields = $value;
         }

         get => $this->clean_fields;
     }

     /**
      * Fields defined on a model pending state validation. These are used only internally
      * */
     private array $fields = []{
         set(array $value){
             $this->fields = array_merge($this->fields, $value);
         }

         get => $this->fields;
     }

     //an array of the names of the fields defined in the model
     private array $defined_field_names = [];

     //an array of the names of the fields not defined in the model
     private array $audit_field_names = [];

     //an array of the names of all the virtual fields
     private array $virtual_field_names = [];

     //an array of the names of the file fields defined in the model
     private array $file_field_names = [];

     /**
      * an array of all the field names that must be included in select clause 
      * to support file fields callback path, rename, url and default_path functions
      * */
     private array $file_required_fields = [];

     private array $unique_constraints = [];

     //add or remove creator and modifier fields depending on with_user_audit setting
     private function get_user_audit_fields(bool $switch = true) : array {
         if($this->_with_user_audit){
             $fields = [
                 'author' => self::author_field()->column($this->get_author_column()),
                 'modifier' => self::modifier_field()->column($this->get_modifier_column())
             ];

             if($this->_with_soft_delete){
                 $fields['remover'] = self::remover_field()->column($this->get_remover_column());
             }

             return $fields;
         }

         return [];
     }

     //add or remove created at and modified at date time stamps depending on with_timestamps setting
     private function get_timestamp_fields(bool $switch = true) : array {
         if($this->_with_timestamps){
             $fields = [
                 'created_at' => self::created_at_field()->column($this->get_created_at_column()),
                 'modified_at' => self::modified_at_field()->column($this->get_modified_at_column())
             ];

             if($this->_with_soft_delete){
                 $fields['removed_at'] = self::removed_at_field()->column($this->get_removed_at_column());
             }

             return $fields;
         }

         return [];
     }

     //add or remove soft delete fields depending on with_soft_delete setting
     private function get_delete_fields(bool $switch = true) : array {
         if($this->_with_soft_delete){
             return [
                 'is_removed' => self::is_removed_field()->column($this->get_is_removed_column())
             ];
         }

         return [];
     }

     public function add_audit_fields(){
         $audit_fields = array_merge(
             $this->get_user_audit_fields(),
             $this->get_timestamp_fields(),
             $this->get_delete_fields()
         );

         $this->audit_field_names = array_keys($audit_fields);

         $fields_add = [];

         foreach($audit_fields as $audit_field_name => $audit_field){
             if(!array_key_exists($audit_field_name, $this->fields)){
                 $fields_add[$audit_field_name] = $audit_field;
             }
         }

         $this->fields = array_merge($this->fields, $fields_add);
     }

     public function set_table_defaults($model_class){
         $this->model_class = $model_class;
         $this->_with_user_audit = config('model.with_user_audit');
         $this->_with_timestamps = config('model.with_timestamps');
         $this->_with_soft_delete = config('model.with_soft_delete');
         $this->created_at_column = config('model.created_at_column');
         $this->author_column = config('model.author_column');
         $this->modified_at_column = config('model.modified_at_column');
         $this->modifier_column = config('model.modifier_column');
         $this->is_removed_column = config('model.is_removed_column');
         $this->removed_at_column = config('model.removed_at_column');
         $this->remover_column = config('model.remover_column');
         $this->action_on_duplicate(config('model.action_on_duplicate'));
     }

     public function clean_model_fields(){

         //create primary key field
         $pk = new Pk($this->pk_type);

         $all_fields = array_merge([
             $this->pk_name => $pk->resolve()
         ], $this->fields);

         $clean_fields = [];

         foreach($all_fields as $n => $v){

             //assert field instance
             Assert::isInstanceOf($v, IField::class, $n.' is not a field instance!');

             $field = $v instanceof ManyRelation ? $v->resolve() : $v;
            
             $field->build(name: $n, model_class: $this->model_class, model_pk: $this->pk_name);
             
             //each field knows to validate its own state
             if(!$field->is_state_valid()){
                 $errors = implode("\n", $field->get_errors());
                 throw new RuntimeException("The field: {$n} defined on the model: {$this->model_class} is not correctly defined!\n {$errors}");
             }

             $clean_fields[$n] = $field;
         }

         $this->clean_fields = $clean_fields;
     }

     public function get_unique_constraints(){
         return $this->unique_constraints;
     }

     public function set_unique_constraints(){
         $constraints = [];
         $model_name = strtolower(array_slice(explode('\\', $this->model_class), -1)[0]);

         foreach($this->unique_field_names as $name){
             $unique_constraint_name = "{$model_name}_{$name}_unique";
             $column_name = $this->clean_fields[$name]->get_column();
             $constraints[$unique_constraint_name] = [$column_name];
         }
         
         foreach($this->_unique_fields as $ucn => $unq_fields){
             $constraints[$ucn] = [];
             foreach($unq_fields as $uf){
                 $column_name = $this->clean_fields[$uf]->get_column();
                 $constraints[$ucn][] = $column_name;
             }
         }

         $this->unique_constraints = $constraints;
     }

     public function set_table_and_connection(string $table_name, string $connection_name){
         $this->table_name = $table_name;
         $this->connection_name = $connection_name;
     }


     public function primary_key(string $name, ?string $type = null){
         $this->pk_name = $name;
         $this->pk_type = $type ?? config('model.pk_type');
     }

     public function temporary(bool $temporary = true){
         $this->_temporary = $temporary;
     }

     public function is_temporary(){
         return $this->_temporary;
     }

     public function get_pk_name(){
        return $this->pk_name;
     }

     public function get_pk_type(){
        return $this->pk_type;
     }

     public function get_name_property(){
         return $this->_name_property;
     }

     public function name_property(string $name_property){
         $this->_name_property = $name_property;
     }

     public function get_table_name(){
         return $this->table_name;
     }

     public function get_connection_name(){
         return $this->connection_name;
     }

     public function with_user_audit(bool $user_audit = true){
         $this->_with_user_audit = $user_audit;
     }

     public function with_timestamps(bool $timestamps = true){
         $this->_with_timestamps = $timestamps;
     }

     public function with_soft_delete(bool $soft_delete = true){
         $this->_with_soft_delete = $soft_delete;
     }

     public function has_user_audit(){
         return $this->_with_user_audit;
     }

     public function has_timestamps(){
         return $this->_with_timestamps;
     }

     public function has_soft_delete(){
         return $this->_with_soft_delete;
     }

     public function get_nav_field_names(){
         return $this->nav_field_names;
     }

     public function get_fk_field_names(){
         return $this->fk_field_names;
     }

     public function get_field_column_refs(){
         return $this->field_column_refs;
     }

     public function get_table_column_names(){
         return $this->table_column_names;
     }

     public function get_created_at_column(){
         return $this->created_at_column;
     }

     public function get_author_column(){
         return $this->author_column;
     }

     public function get_modified_at_column(){
         return $this->modified_at_column;
     }

     public function get_modifier_column(){
         return $this->modifier_column;
     }

     public function get_removed_at_column(){
         return $this->removed_at_column;
     }

     public function get_remover_column(){
         return $this->remover_column;
     }

     public function get_is_removed_column(){
         return $this->is_removed_column;
     }

     public function get_action_on_duplicate(){
         return $this->_action_on_duplicate;
     }

     public function action_on_duplicate(string $action){
         //ensure action is among the valid options
         $actions = ['ABORT_WITH_ERROR', 'INSERT_MINUS_DUPLICATE', 'UPDATE_ON_DUPLICATE', 'RETURN_EXISTING'];
         if(!in_array($action, $actions)){
             throw new RuntimeException('The duplicate action provided is not valid. Valid duplicate actions are: '.implode(',', $actions));
         }
         $this->_action_on_duplicate = $action;
     }

     public function get_unique_field_names(){
        return $this->unique_field_names;
     }

     public function unique_fields(array $fields){
         $this->_unique_fields = $fields;
     }

     public function get_clean_fields(){
         return $this->clean_fields;
     }

     public function fields(array $fields){
         $this->fields = $fields;
     }

     public function get_defined_field_names(){
         return $this->defined_field_names;
     }

     public function get_audit_field_names(){
         return $this->audit_field_names;
     }

     public function get_virtual_field_names(){
         return $this->virtual_field_names;
     }

     public function get_file_field_names(){
         return $this->file_field_names;
     }

     public function get_file_required_fields(){
        return $this->file_required_fields;
     }

     //field creation methods

     public static function many_of(string $model, ?string $local_key = null, ?string $foreign_key = null): IField {
         return new ManyRelation(model: $model, local_key: $local_key, foreign_key: $foreign_key);
     }

     public static  function one_of(string $model, ?string $local_key = null, ?string $foreign_key = null): IField {
         return new OneToOne(related_model: $model, local_key: $local_key, foreign_key: $foreign_key);
     }

     public static function boolean_field(...$kwargs): IField {
         return new BooleanField(...$kwargs);
     }

     public static function char_field(...$kwargs): IField {
         return new CharField(...$kwargs);
     }

     public static function choice_field(...$kwargs): IField {
         return new ChoiceField(...$kwargs);
     }

     public static function date_field(...$kwargs): IField {
         return new DateField(...$kwargs);
     }

     public static function datetime_field(...$kwargs): IField {
         return new DateTimeField(...$kwargs);
     }

     public static function decimal_field(...$kwargs): IField {
         return new DecimalField(...$kwargs);
     }

     public static function email_field(...$kwargs): IField {
         return new EmailField(...$kwargs);
     }

     public static function file_field(...$kwargs): IField {
         return new FileField(...$kwargs);
     }

     public static function float_field(...$kwargs): IField {
         return new FloatField(...$kwargs);
     }

     public static function integer_field(...$kwargs): IField {
         return new IntegerField(...$kwargs);
     }

     public static function ip_address_field(...$kwargs): IField {
         return new IpAddressField(...$kwargs);
     }

     public static function json_field(...$kwargs): IField {
         return new JsonField(...$kwargs);
     }

     public static function password_field(...$kwargs): IField {
         return new PasswordField(...$kwargs);
     }

     public static function phone_field(...$kwargs): IField {
         return new PhoneField(...$kwargs);
     }

     public static function slug_field(...$kwargs): IField {
         return new SlugField(...$kwargs);
     }

     public static function text_field(...$kwargs): IField {
         return new TextField(...$kwargs);
     }

     public static function time_field(...$kwargs): IField {
         return new TimeField(...$kwargs);
     }

     public static function url_field(...$kwargs): IField {
         return new UrlField(...$kwargs);
     }

     public static function uuid_field(...$kwargs): IField {
         return new UuidField(...$kwargs);
     }

     public static function image_field(...$kwargs): IField {
         return new ImageField(...$kwargs);
     }

     public static function virtual_field(...$kwargs): IField {
         return new VirtualField(...$kwargs);
     }

     public static function video_field(...$kwargs): IField {
         return new VideoField(...$kwargs);
     }

     private static function assert_auth_model_exists(string $field) : string {

         $model_class = config('auth.model_class');

         if($model_class && class_exists($model_class) && is_subclass_of($model_class, Model::class)){
             return $model_class;
         }

         throw new RuntimeException("Cannot creat {$field} field. The user model has not been defined!");
     }

     public static function author_field() : IField {
         $auth_model = self::assert_auth_model_exists('author');
         return self::one_of($auth_model, foreign_key: $auth_model::get_pk_name())->column(config('model.author_column'));
     }

     public static function modifier_field() : IField {
         $auth_model = self::assert_auth_model_exists('modifier');
         return self::one_of($auth_model, foreign_key: $auth_model::get_pk_name())->column(config('model.modifier_column'));
     }

     public static function remover_field() : IField {
         $auth_model = self::assert_auth_model_exists('remover');
         return self::one_of($auth_model, foreign_key: $auth_model::get_pk_name())->column(config('model.remover_column'));
     }

     public static function created_at_field() : IField {
         return self::datetime_field()->column(config('model.created_at_column'));
     }

     public static function modified_at_field() : IField {
         return self::datetime_field()->column(config('model.modified_at_column'));
     }

     public static function removed_at_field() : IField {
         return self::datetime_field()->column(config('model.removed_at_column'));
     }

     public static function is_removed_field() : IField {
         return self::boolean_field()->column(config('model.is_removed_column'));
     }

}
