<?php
namespace SaQle\Views;

use SaQle\Orm\Entities\Field\Controls\FormControl;
use Closure;

class EditRow{
	/**
	 * The display label for edit row
	 * @var string
	 */
	 private string $label;

	 /**
	 * Edit row value
	 * @var Closure | Scalar
	 */
	 private Closure | string $value;

	 /**
	 * Edit row form control
	 * @var FormControl
	 */
	 private ?FormControl $control;

	 /**
	 * Edit row value function. Pass the value through this function to transform it
	 * @var Closure
	 */
	 private ?Closure $value_fnc;

	 /**
	 * Edit row display function. Custom display for edit row, especially for arrays and object values
	 * @var Closure
	 */
	 private ?Closure $display_fnc;

	 /**
	 * The type of data to display on this row. Usually file or scalar
	 * @var string type
	 */
	 private string $type;

	 /**
	 * Whether the value displayed here is editable or not.
	 * @var bool | Closure
	 */
	 private bool | Closure $_is_editable = false;
	 
	 /**
	 * Create a new form group instance
	 * @param string      $label
	 * @param mixed       $value
	 * @param string      $type
	 * @param Closure     $value_fnc
	 * @param Closure     $display_fnc
	 * @param FormControl $control
	 */
	 public function __construct(string $label, Closure | string $value, string $type = "scalar", ?Closure $value_fnc = null, ?Closure $display_fnc = null, ?FormControl $control = null){
		 $this->label       = $label;
		 $this->value       = $value;
		 $this->type        = $type;
		 $this->value_fnc   = $value_fnc;
		 $this->display_fnc = $display_fnc;
		 $this->control     = $control;
	 }

	 public function get_label(){
	 	return $this->label;
	 }

	 public function get_value(){
	 	return $this->value;
	 }

	 public function get_control(){
	 	return $this->control;
	 }

	 public function get_value_function(){
	 	return $this->value_fnc;
	 }

	 public function get_display_function(){
	 	return $this->display_fnc;
	 }

	 public function get_row($data_source){
	 	 #get the value first.
	 	 $value = $this->walk_get_value($data_source);
	 	 #if a custom display function has been set, call it.
	 	 if($this->display_fnc){
	 	 	$fnc = $this->display_fnc;
	 	 	return $fnc($value);
	 	 }

         #provide editing controls if editing is allowed and a form control is provided.
         $is_editable = $this->_is_editable;
         if(is_callable($is_editable)){
         	$is_editable = $is_editable();
         }

         $edit_button  = "";
         $edit_control = "";
         if($is_editable && $this->control){
         	 $edit_button  = "<span title='Edit {$this->label}' class='information-row-editbutton'><i data-lucide='square-pen'></i></span>";
         	 $this->control->set_properties( array_merge($this->control->get_properties(), ['placeholder' => $value]) );
         	 $edit_control = $this->control->get_control();
         }

         #if this is a file, convert the value to a file icon view.
         if($this->type == "file" || ($this->control && $this->control->get_type() == "file")){
         	 $value = $this->get_file_rep($value, $this->get_label());
         }

         #by default, hide the control and show the value, this could change in future determined by certain conditions.
         $hide_control = "hide";
		 $hide_value   = "";

	 	 return "
	 	 <div class='flex v_center information-row'>
             <div class='flex v_center information-row-label'>
                 {$this->label}
             </div>
             <div class='flex v_center information-row-value'>
                 <div class='{$hide_value} flex v_center current_value'>
                     {$value}
                 </div>
                 <div class='{$hide_control} theme_padding_top edit_value'>
                     {$edit_control}
                 </div>
             </div>
             <div class='flex v_center row_reverse information-row-options'>
                 {$edit_button}
             </div>
         </div>
	 	 ";
	 }

	 private function walk_get_value($object){
         #get value from object and value property
	 	 if(is_callable($this->value)){
	 	 	$valfnc = $this->value;
	 	 	$value  = $valfnc($object);
	 	 }else{
	 	 	$properties = explode(".", $this->value);
		 	 $value      = $object;
		 	 for($p = 0; $p < count($properties); $p++){
		 		 $cp = $properties[$p];
		 		 if(!is_null($value)){
		 		 	$value = is_numeric($cp) ? $value[$cp] : $value->$cp;
		 		 }else{
		 		 	break;
		 		 }
		 	 }
	 	 }

	 	 #if the value_fnc is provided, format the value and return to caller.
	 	 if($this->value_fnc){
	 	 	$fnc   = $this->value_fnc;
	 	 	$value = $fnc($value);
	 	 }
	 	 return $value;
	 }

	 private function get_file_rep($url, $title){
	 	 $patharray = explode(".", $url);
	 	 $extension = strtolower(end($patharray));
	 	 $icon = match($extension){
		    'jpg', 'png', 'jpeg' =>  "<img src='{$url}'><!--<a class='vieweditrowdoclink' href='#' data-title='{$title}' data-type='image' data-url='{$url}'>Open image</a>-->",
		    'pdf'                =>  "<img src='{{ layout_image_path }}/pdficon.png'><a class='vieweditrowdoclink' href='#' data-title='{$title}' data-type='pdf' data-url='{$url}'>Open document</a>",
		    default              =>  "<img src='{{ layout_image_path }}/fileicon.png'><a class='vieweditrowdoclink' href='#' data-title='{$title}' data-type='general' data-url='{$url}'>Open file</a>",
		 };
		 return $icon;
	 }

	 public function set_label(string $label){
	 	 $this->label = $label;
	 }

	 public function set_value(Closure | string $value){
	 	 $this->value = $value;
	 }

	 public function set_control(FormControl $control){
	 	 $this->control = $control;
	 }

	 public function set_value_function(Closure $func){
	 	return $this->value_fnc = $func;
	 }

	 public function set_display_function(Closure $func){
	 	return $this->display_fnc = $func;
	 }

	 /**
	  * Set whether an edit row should be editable or not. 
	  * @param bool | Closure
	  * */
	 public function is_editable(Closure | bool $is_editable){
	 	$this->_is_editable = $is_editable;
	 }
}
?>