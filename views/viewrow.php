<?php
namespace SaQle\Views;

use SaQle\Orm\Entities\Field\Controls\FormControl;
use Closure;
use stdClass;

class ViewRow{
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
	 * View row form control, view group or view group collection
	 * @var FormControl | ViewGroup | ViewGroupCollection
	 */
	 private FormControl | ViewGroup | ViewGroupCollection | NULL $control;

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
	 * Data source for this view row.
	 * @var stdClass
	 */
	 private ?stdClass $sdata = null;

	 /**
	 * Data source for this view row.
	 * @var stdClass
	 */
	 private ?stdClass $del_data = null;

	 /**
	 * Whether to display the value coming from sdata on the control
	 * @var bool
	 */
	 private bool $show_value = false;
	 
	 /**
	 * Create a new form group instance
	 * @param string      $label
	 * @param mixed       $value
	 * @param string      $type
	 * @param Closure     $value_fnc
	 * @param Closure     $display_fnc
	 * @param FormControl $control
	 */
	 public function __construct(string $label, Closure|string $value, FormControl|ViewGroup|ViewGroupCollection|NULL $control, string $type = "scalar", ?Closure $value_fnc = null, ?Closure $display_fnc = null, ?stdClass $sdata = null, ?stdClass $del_data = null){
		 $this->label       = $label;
		 $this->value       = $value;
		 $this->type        = $type;
		 $this->value_fnc   = $value_fnc;
		 $this->display_fnc = $display_fnc;
		 $this->control     = $control;
		 $this->sdata       = $sdata;
		 $this->del_data    = $del_data;
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

	 public function get_show_value(){
	 	return $this->show_value;
	 }

     private function get_formcontrol_row($view_form_id = "", $delete_form_id = ""){
     	 #get the value first.
	 	 $value = $this->walk_get_value($this->sdata);
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
         	 $edit_button  = "<span title='Edit {$this->label}' class='information-row-editbutton'><span class='fas fa-pen fa-fw'></span></span>";
         	 $control_properties = $this->control->get_properties();
         	 $additional_properties = ['placeholder' => $value, 'form' => $view_form_id];
         	 if($this->show_value){
         	 	$additional_properties['default'] = $value;
         	 }
         	 $this->control->set_properties(array_merge($control_properties, $additional_properties));
         	 $edit_control = $this->control->get_control();
         }

         #if this is a file, convert the value to a file icon view.
         if($this->type == "file" || ($this->control && $this->control->get_type() == "file")){
         	 $value = $this->get_file_rep($value, $this->get_label());
         }

         #by default, hide the control and show the value, this could change in future determined by certain conditions.
         $hide_control = !is_null($value) ? "hide" : "";
		 $hide_value   = !is_null($value) ? "" : "hide";
		 $hide_button  = !is_null($value) ? "" : "hide";

	 	 return "
	 	 <div class='flex v_center information-row information-row-threecols'>
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
             <div class='{$hide_button} flex v_center row_reverse information-row-options'>
                 {$edit_button}
             </div>
         </div>
	 	 ";
     }

     private function get_viewgroup_row($view_form_id = "", $delete_form_id = ""){
     	 $groupview = $this->control->construct_group($this->sdata, $view_form_id, $delete_form_id);
	 	 return "
	 	 <div class='flex v_center information-row information-row-twocols'>
             <div class='flex v_center information-row-label'>
                 {$this->label}
             </div>
             <div class='information-row-value'>
                 {$groupview}
             </div>
         </div>
	 	 ";
     }

     private function get_viewgroupcollection_row($view_form_id = "", $delete_form_id = ""){
     	 $groupcollectionview = $this->control->construct_groups($this->sdata, $view_form_id, $delete_form_id);
	 	 return "
	 	 <div class='flex v_center information-row information-row-twocols'>
             <div class='flex v_center information-row-label'>
                 {$this->label}
             </div>
             <div class='information-row-value'>
                 {$groupcollectionview}
             </div>
         </div>
	 	 ";
     }

	 public function get_row($view_form_id = "", $delete_form_id = ""){
	 	 $control_class = $this->control ? get_class($this->control) : "Undefined";
	 	 return match($control_class){
	 	 	'SaQle\Orm\Entities\Field\Controls\FormControl', 'Undefined' => $this->get_formcontrol_row($view_form_id, $delete_form_id),
	 	 	'SaQle\Views\ViewGroupCollection'      => $this->get_viewgroupcollection_row($view_form_id, $delete_form_id),
	 	 	'SaQle\Views\ViewGroup'                => $this->get_viewgroup_row($view_form_id, $delete_form_id)
	 	 };
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
		 		 	if(is_numeric($cp)){
		 		 		$value = $value[$cp] ?? null;
		 		 	}else{
		 		 		$value = $value->$cp ?? null;
		 		 	}
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

	 public function set_show_value(bool $show_value){
	 	$this->show_value = $show_value;
	 }

	 /**
	  * Set whether an edit row should be editable or not. 
	  * @param bool | Closure
	  * */
	 public function is_editable(Closure | bool $is_editable){
	 	$this->_is_editable = $is_editable;
	 }

	 public function get_sdata(){
	 	return $this->sdata;
	 }

	 public function get_del_data(){
	 	return $this->del_data;
	 }
}
?>