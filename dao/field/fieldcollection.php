<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field;

use SaQle\Dao\Field\Interfaces\IField;

class FieldCollection implements IField{
	 function __construct(private array $fields = []){

	 }
	 public function add(IField $field){
		 $this->fields[] = $field;
	 }
	 public function remove(string $field_name){
		 $field_index = -1;
		 for($f = 0; $f < count($this->fields); $f++){
			 if($this->fields[$f]->get_name() == $field_name){
				 $field_index = $f;
				 break;
			 }
		 }
		 if($field_index !== -1) unset($this->fields[$field_index]);
		 $this->fields = array_merge($this->fields);
	 }
	 public function set_field_value($name, $value){
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->get_name() == $name){
				 $field_type = $this->fields[$c]->get_type();
				 if($field_type === "FILE" && !is_null($value)){
					 $this->fields[$c]->set_value( (is_array($value) && array_key_exists('name', $value)) ? (is_array($value['name']) ? implode(",", $value['name']) : $value['name']) : $value);
				 }else{
					 $this->fields[$c]->set_value($value);
				 }
				 break;
			 }
		 }
	 }
	 public function get_fields(){
		 return $this->fields;
	 }
	 public function get_field_names(){
	 	 $names = [];
	 	 foreach($this->fields as $f){
	 	 	$names[] = $f->get_name();
	 	 }
		 return $names;
	 }
	 public function get_field($field_name){
		 $field_index = -1;
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->get_name() == $field_name){
				 $field_index = $c;
			 }
		 }
		 return $this->fields[$field_index]->get_value();
	 }
	 public function get_primary_key_field(){
	 	 $field = null;
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_primary_key()){
				 $field = $this->fields[$c];
			 }
		 }
		 return $field;
     }
     public function get_primary_key_name() : string{
     	 $field_name = null;
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_primary_key()){
				 $field_name = $this->fields[$c]->get_name();
			 }
		 }
		 return $field_name;
     }

     public function get_validation_configurations(?array $fields = null) : array{
     	 $vc = [];
     	 $field_types = ['string' => 'text', 'int' => 'int', 'float' => 'float'];
	 	 foreach($this->fields as $f){
	 	 	 $field_name       = $f->get_name();
	 	 	 $field_type       = $f->get_type();
	 	 	 $field_attributes = $f->get_attributes();
	 	 	 //$field_vc       = ['type' => $field_types[$field_type] ?? ""];
	 	 	 $field_vc         = ['type' => $field_type];
	 	 	 foreach($field_attributes as $key => $attr){
	 	 	 	if(in_array($key, ['SaQle\Dao\Field\Attributes\TextFieldValidation', 'SaQle\Dao\Field\Attributes\NumberFieldValidation', 'SaQle\Dao\Field\Attributes\FileFieldValidation']) ){
	 	 	 		$field_vc = array_merge($field_vc, $attr);
	 	 	 		if($key === 'SaQle\Dao\Field\Attributes\FileFieldValidation'){
	 	 	 			$field_vc['type'] = 'file';
	 	 	 		}
	 	 	 	}
	 	 	 }
	 	 	 if(is_null($fields) || (!is_null($fields) && count($fields) > 0 && array_search($field_name, $fields) !== false)){
	 	 	 	 $vc[$field_name] = $field_vc;
	 	 	 }
	 	 }
		 return $vc;
     }

     public function get_file_configurations() : array{
     	 $fc = [];
	 	 foreach($this->fields as $f){
	 	 	 $field_name       = $f->get_name();
	 	 	 $field_attributes = $f->get_attributes();
	 	 	 $field_fc         = null;
	 	 	 $is_file          = false;
	 	 	 foreach($field_attributes as $key => $attr){
	 	 	 	if(in_array($key, ['SaQle\Dao\Field\Attributes\FileField', 'SaQle\Dao\Field\Attributes\FileFieldValidation'])){
	 	 	 		$is_file = true;
	 	 	 		if($key === 'SaQle\Dao\Field\Attributes\FileField'){
	 	 	 			$field_fc = array_merge([], $attr);
	 	 	 		}
	 	 	 	}
	 	 	 }
	 	 	 if($is_file){
	 	 	 	 $fc[$field_name] = $field_fc;
	 	 	 }
	 	 }
		 return $fc;
     }

     public function get_navigation_fields(){
     	 $navigation_fields = [];
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_navigation_field()){
				 $navigation_fields[] = $this->fields[$c];
			 }
		 }
		 return $navigation_fields;
     }

     public function get_navigation_field_names(){
     	 $navigation_field_names = [];
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_navigation_field()){
				 $navigation_field_names[] = $this->fields[$c]->get_name();
			 }
		 }
		 return $navigation_field_names;
     }

     public function get_foreign_keys(){
     	 $foreign_keys = [];
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_foreign_key()){
				 $foreign_keys[] = $this->fields[$c];
			 }
		 }
		 return $foreign_keys;
     }

     public function get_foreign_key_names(){
     	 $foreign_key_names = [];
		 for($c = 0; $c < count($this->fields); $c++){
			 if($this->fields[$c]->is_foreign_key()){
				 $foreign_key_names[] = $this->fields[$c]->get_name();
			 }
		 }
		 return $foreign_key_names;
     }

     public function get_include_field(string $incoming_field_name) : Field | null{
     	 $field = null;
		 for($c = 0; $c < count($this->fields); $c++){
		 	 $current_field_name = $this->fields[$c]->get_name();
			 if($incoming_field_name === $current_field_name 
			 	&& ($this->fields[$c]->is_foreign_key() || $this->fields[$c]->is_navigation_field())){
			 	 $field = $this->fields[$c];
				 break;
			 }
		 }
		 return $field;
     }
}

?>