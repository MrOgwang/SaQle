<?php
declare(strict_types = 1);
namespace SaQle\Dao\Field\Controls;

use Attribute;
use SaQle\Dao\Field\Interfaces\IControl;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FormControl implements IControl{
	 private string $type;
	 private string $label;
	 private string $name;
	 private        $properties;
	 public function __construct($type, $label, $name, ...$properties){
	 	$this->type       = $type;
	 	$this->name       = $name;
	 	$this->label      = $label;
	 	$this->properties = $properties;
	 }

     public function get_type(){
	 	return $this->type;
	 }

	 public function get_label(){
	 	return $this->label;
	 }

	 public function get_name(){
	 	return $this->name;
	 }

	 public function get_properties(){
	 	return $this->properties;
	 }

	 public function set_type(){
	 	$this->type = $type;
	 }

	 public function set_label(){
	 	$this->label = $label;
	 }

	 public function set_name($name){
	 	$this->name = $name;
	 }

	 public function set_properties($properties){
	 	$this->properties = $properties;
	 }

	 

     /**
      * Set form control as required
      * */
	 private function set_required(){
	 	return array_key_exists("required", $this->properties) ? "required='required'" : "";
	 }

     /**
      * Set form control as disabled
      * */
	 private function set_disabled(){
	 	return array_key_exists("disabled", $this->properties) ? "disabled='disabled'" : "";
	 }

	 /**
      * Set form control as readonly
      * */
	 private function set_readonly(){
	 	return array_key_exists("readonly", $this->properties) ? "readonly" : "";
	 }

	 /**
      * Set step attribute for numeric controls
      * */
	 private function set_step(){
	 	return array_key_exists("step", $this->properties) ? "step='".$this->properties['step']."'" : "";
	 }

	 /**
      * Set placeholder attribute for input controls
      * */
	 private function set_placeholder(){
	 	return array_key_exists("placeholder", $this->properties) ? "placeholder='".$this->properties['placeholder']."'" : "";
	 }

	 /**
      * Set form attribute for input controls
      * */
	 private function set_form(){
	 	return array_key_exists("form", $this->properties) && $this->properties['form'] ? "form='".$this->properties['form']."'" : "";
	 }

	 /**
      * Set multiple attribute for input controls
      * */
	 private function set_multiple(){
	 	return array_key_exists("multiple", $this->properties) && $this->properties['multiple'] ? "multiple" : "";
	 }

     public function get_control(){
     	 return match($this->type){
		     'radio'    => $this->get_radio_control(),
		     'checkbox' => $this->get_checkbox_control(),
		     'file'     => $this->get_file_control(),
		     'textarea' => $this->get_textarea_control(),
		     'select'   => $this->get_select_control(),
		     'slider'   => $this->get_slider_control(),
		     default    => $this->get_input_control(),
         };
     }

     public function get_radio_control(){
     	return "";
     }

     public function get_checkbox_control(){
     	 $inline  = $this->properties["inline"] ?? false;
     	 $options = $this->properties["options"] ?? [];
     	 $options_view = "";
     	 foreach($options as $k => $v){
     	 	$options_view .= "
     	 	<div>
     	 	     <input {$this->set_disabled()} {$this->set_form()} class='{$k} form_input_check' id='{$k}' type='checkbox' name='{$this->name}[]' value='{$k}'> 
     	 	     <label class='form_input_label' for='{$k}'>{$v}</label>
     	 	</div>
     	 	";
     	 }
		 return "
		 <div class='form-control-group'>
		    <div class='form-control-label'>
		        <h4>{$this->label}</h4>
		    </div>
		    <div class='form-control-field'>
		        {$options_view}
		    </div>
		</div>
		 ";
     }

     public function get_file_control(){
     	 $file_name = 'No file selected';
     	 $accept = array_key_exists("accept", $this->properties) ? 
     	 (is_array($this->properties['accept']) ? "accept='".implode(',', $this->properties['accept'])."'" : "accept='{$this->properties['accept']}'") 
     	 : "";
     	 $input_name = array_key_exists("multiple", $this->properties) && $this->properties['multiple'] ? $this->name."[]" : $this->name;
     	 return "
		 <div class='form-control-group'>
		    <div class='form-control-label'>
		        <h4>{$this->label}</h4>
		    </div>
		    <div class='form-control-field'>
		        <div class='flex v_center form-control-file-name'>
		            <button class='flex v_center'><i data-lucide='view'></i></button>
		            <span class='flex v_center'>{$file_name}</span>
		            <label for='{$this->name}' class='flex v_center'><i data-lucide='cloud-upload'></i>&nbsp;Browse</label>
		        </div>
		        <input {$this->set_multiple()} {$this->set_form()} class='{$this->name} file-control-field' {$this->set_disabled()} {$this->set_required()} id='{$this->name}' name='{$input_name}' {$accept} type='{$this->type}'>
		    </div>
		</div>
		 ";
     }

     public function get_textarea_control(){
     	 $default = $this->properties["default"] ?? "";
		 return "
		 <div class='form-control-group'>
		    <div class='form-control-label'>
		        <h4>{$this->label}</h4>
		    </div>
		    <div class='form-control-field'>
		        <textarea {$this->set_form()} {$this->set_placeholder()} {$this->set_disabled()} {$this->set_required()} id='{$this->name}' name='{$this->name}' class='{$this->name}'>{$default}</textarea>
		    </div>
		</div>
		 ";
     }

     public function get_select_control(){
     	 $default = $this->properties["default"] ?? [];
     	 /**
     	  * Select options: can be,
     	  * 1. A key => value array of options
     	  * 2. A function that returns a key => value array of options
     	  * 3. A function that returns a preformatted html string of options
     	  * 4. A string representing a class and a method to call to get an array|string of options in the format classname@method
     	  * 5. A string representing preformatted html options.
     	  * */
     	 $multiple     = array_key_exists("multiple", $this->properties) ? "multiple data-multi-select" : "";
     	 $options      = $this->properties["options"] ?? null;
     	 $options_view = "";
     	 if($options){
     	 	 if(is_callable($options)){
     	 	 	 $options = $options(); //this will result into a string or an array
     	 	 }elseif(is_string($options) && str_contains($options, "@")){
 	 	 		 [$class_name, $method_name] = explode("@", $options);
 	 	 		 #is the method defined in the class.
 	 	 		 if(!method_exists($class_name, $method_name)){
	 	 			 throw new \Exception("@METHOD {$method_name} not defined within the class {$class_name} as provided for select control options!");
	 	 		 }
	 	 		 $options = (new $class_name())->$method_name();
     	 	 }

     	 	 if(is_array($options)){
     	 	     $data         = $this->properties["data"] ?? [];
     	 	     $options_view = $multiple ? "" : "<option value=''>--Select--</option>";
		     	 foreach($options as $k => $v){
		     	 	$selected  = in_array($k, $default) ? "selected" : "";
		     	 	$item_data = isset($data[$k]) ? "data-option_data='".json_encode($data[$k])."'" : "";
		     	 	$options_view .= "<option {$selected} {$item_data} value='{$k}'>{$v}</option>";
		     	 }
     	     }else{
     	     	 $options_view = $options;
     	     }
     	 }
     	 
     	 $controlname = $multiple ? $this->name : $this->name;
		 return "
		 <div class='form-control-group'>
		    <div class='form-control-label'>
		        <h4>{$this->label}</h4>
		    </div>
		    <div class='form-control-field'>
		        <select {$multiple} {$this->set_form()} {$this->set_placeholder()} {$this->set_disabled()} {$this->set_required()} id='{$this->name}' name='{$controlname}' class='{$this->name}'>
		            {$options_view}
		        </select>
		    </div>
		</div>
		 ";
     }

     public function get_slider_control(){
     	return "";
     }

	 public function get_input_control(){
	 	 $default = $this->properties["default"] ?? "";
		 return "
		 <div class='form-control-group'>
		    <div class='form-control-label'>
		        <h4>{$this->label}</h4>
		    </div>
		    <div class='form-control-field'>
		        <input {$this->set_form()} {$this->set_placeholder()} {$this->set_readonly()} {$this->set_step()} {$this->set_disabled()} {$this->set_required()} id='{$this->name}' name='{$this->name}' value='{$default}' type='{$this->type}' class='{$this->name}'>
		    </div>
		</div>
		 ";
	 }
}
?>