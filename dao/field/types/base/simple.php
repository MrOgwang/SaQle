<?php

namespace SaQle\Dao\Field\Types\Base;

use SaQle\Dao\Field\Types\{Pk, TextType, NumberType, FileField, TimestampField};
use SaQle\Dao\Field\Types\Base\Relation;

abstract class Simple{
	 //The name of the field as defined in the model
	 public string $field_name = '' {
	 	 set(string $value){
	 	 	 $this->field_name = $value;
	 	 	 if(!$this->column_name){
	 	 	 	 $this->column_name = $value;
	 	 	 }
	 	 }

	 	 get => $this->field_name;
	 }

	 //The name of the table column to associate with this field
	 public protected(set) string $column_name = '' {
	 	 set(string $value){
	 	 	 $this->column_name = $value;
	 	 }

	 	 get => $this->column_name;
	 }

	 //Whether this field is required or not. If true, allow_null will be false
	 public bool $required = false {
	 	 set(bool $value){
	 	 	 $this->required = $value;
	 	 }

	 	 get => $this->required;
	 }

	 //The primitive data type for the content stored in this field
	 public string $primitive_type {
	 	 set(string $value){
	 	 	 $this->primitive_type = $value;
	 	 }

	 	 get => $this->primitive_type;
	 }

	 //The table column type for the content stored in this field
	 public string $column_type {
	 	 set(string $value){
	 	 	 $this->column_type = $value;
	 	 }

	 	 get => $this->column_type;
	 }

	 //The validation type for the content stored in this field
	 public string $validation_type {
	 	 set(string $value){
	 	 	 $this->validation_type = $value;
	 	 }

	 	 get => $this->validation_type;
	 }

	 //The actual, unmodified content of this field
	 public mixed $content = '' {
	 	 set(mixed $value){
	 	 	 /**
	 	 	  * Validate the content first before assigning
	 	 	  * */
	 	 	 $this->content = $value;

	 	 	 /**
	 	 	  * For files,
	 	 	  * 1. Save the file to a temporary location
	 	 	  * 2. Rename the file using the rename method
	 	 	  * 3. Resize the file if resize 
	 	 	  * */
	 	 }

	 	 get => $this->content;
	 }

	 //Whether to allow null content, works for text, numbers and files.
	 public bool $null = true {
	 	 set(bool $value){
	 	 	 $this->null = $value;
	 	 }

	 	 get => $this->null;
	 }

	 /**
	 * The maximum value allowed for the content
	 * For text, this counts the number of characters.
	 * For numbers, this is the value 
	 * For files, this is the size
	 * */
	 public mixed $maximum = null {
	 	 set(mixed $value){
	 	 	 $this->maximum = $value;
	 	 }

	 	 get => $this->maximum;
	 }

	 /**
	 * The minimum value allowed for the content
	 * For text, this counts the number of characters.
	 * For numbers, this is the value 
	 * For files, this is the size
	 * */
	 public mixed $minimum = null {
	 	 set(mixed $value){
	 	 	 $this->minimum = $value;
	 	 }

	 	 get => $this->minimum;
	 }

     //Whether the maximum value is inclusive.
	 public ?bool $max_inclusive = null {
	 	 set(?bool $value){
	 	 	 $this->max_inclusive = $value;
	 	 }

	 	 get => $this->max_inclusive;
	 }

	 //Whether the minimum value is inclusive.
	 public ?bool $min_inclusive = null {
	 	 set(?bool $value){
	 	 	 $this->min_inclusive = $value;
	 	 }

	 	 get => $this->min_inclusive;
	 }

	 /**
	 * The number of characters for text,
	 * The number of digits for numbers,
	 * The number of characters in a file name
	 * */
	 public ?int $length = null{
	 	 set(?int $value){
	 	 	 $this->length = $value;
	 	 }

	 	 get => $this->length;
	 }

	 /**
	 * The pattern to match this value against.
	 * For files, this pattern will be matched on file name
	 * */
	 public ?string $pattern = null {
	 	 set(?string $value){
	 	 	 $this->pattern = $value;
	 	 }

	 	 get => $this->pattern;
	 }

	 //whether to consider al files or just one
	 public protected(set) ?bool $compact = null {
	 	 set(?bool $value){
	 	 	 $this->compact = $value;
	 	 }

	 	 get => $this->compact;
	 }
  
     //initialize a new field
	 public function __construct(...$kwargs){
	 	 //echo $this::class."\n";
	 	 //print_r($kwargs);
	 	 foreach($kwargs as $k => $v){
	 	 	 $this->$k = $v;
	 	 }
	 }

     //get a string represtation of this field
	 public function __toString(){
	 	 return $this->content ? (string)$this->content : "";
	 }

	 //get validation key word arguemnts
	 protected function get_validation_kwargs() : array{
	 	 return [
	 	 	 'primitive_type',
	 	 	 'null',
	 	 	 'required',
	 	 	 'maximum',
	 	 	 'minimum',
	 	 	 'max_inclusive',
	 	 	 'min_inclusive',
	 	 	 'length',
	 	 	 'pattern',
	 	 	 'validation_type'
	 	 ];
	 }

	 //get database key word arguments
	 protected function get_db_kwargs() : array{
	 	 return [
	 	 	 'column_type',
	 	 	 'column_name',
	 	 	 'content'
	 	 ];
	 }

     //get validation configurations
	 public function get_validation_configurations() : array{
		 if($this instanceof TextType || $this instanceof NumberType || $this instanceof FileField){
		 	 $validation_kwargs = $this->get_validation_kwargs();
		 	 $get_validation_configurations = [];
		 	 foreach($validation_kwargs as $k){
		 	 	 if(!is_null($this->$k)){
		 	 	 	 $get_validation_configurations[$k] = $this->$k;
		 	 	 }
		 	 }
		 	 return $get_validation_configurations;
		 }
         
         //Primary keys and Relation keys will be by passed for now.
		 return [];
	 }

     //get field database definition
	 public function get_field_definition(){
		 $is_field = $this instanceof Relation && $this->navigation ? false : true;
		 if(!$is_field)
			 return null;

		 $def   = [$this->column_name];
		 $def[] = $this->column_type === "VARCHAR" ? $this->column_type."(".$this->length.")" : $this->column_type;
		 if($this instanceof PK){
		 	 $def[] = $this->column_type === "VARCHAR" ? "PRIMARY KEY" : "AUTO_INCREMENT PRIMARY KEY";
		 }
		 $def[] = $this->required ? "NOT NULL" : "NULL";
		 if($this instanceof TimestampField){
		     $def[] = $this->kwargs['db_auto_init'] ? "DEFAULT CURRENT_TIMESTAMP" : "";
		     $def[] = $this->kwargs['db_auto_update'] ? "ON UPDATE CURRENT_TIMESTAMP" : "";
		 }else{
		 	 $def[] = isset($this->kwargs['value']) ? 'DEFAULT '.$this->kwargs['value'] : '';
		 }
 	 	 return implode(" ", $def);
	 }

	 public function get_kwargs() : array{
	 	 /*$kwargs_keys = array_merge($this->get_validation_kwargs(), $this->get_db_kwargs());
	 	 $kwargs      = [];
	 	 foreach($kwargs_keys as $key){
	 	 	 $kwargs[$key] = $this->$key;
	 	 }
	 	 return $kwargs;*/
	 	 return [];
	 }
}
?>