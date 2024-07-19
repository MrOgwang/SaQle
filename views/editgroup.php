<?php
namespace SaQle\Views;

use SaQle\Dao\Field\Controls\FormControl;
use Closure;

class EditGroup{
	/**
	 * The title of group
	 * @var string
	 */
	 private string $title;

	 /**
	 * The data access object class
	 * @var string
	 */
	 private string $dao;

	 /**
	 * The value source property
	 * @var string
	 */
	 private string $source_property;

	 /**
	 * Edit rows
	 * @var array
	 */
	 private array $rows;

	 /**
	  * Is editable : set the rows of this group as editable.
	  * @var boolean
	  * */
	 private bool $editable = false;
	 
	 /**
	 * Create a new form group instance
	 * @param string $title
	 * @param array  $rows
	 */
	 public function __construct(string $title, string $dao, string $source_property = "", array $rows = []){
		 $this->title           = $title;
		 $this->rows            = $rows;
		 $this->dao             = $dao;
		 $this->source_property = $source_property;
		 if(!$rows){
			 $reflector    = new \ReflectionClass($dao);
	         $properties   = $reflector->getProperties();
			 foreach($properties as $p){
				 $property_name  = $p->getName();
				 $property_value = $p->getDefaultValue();
				 $attributes     = $p->getAttributes(FormControl::class);
				 if(count($attributes) > 0){
				 	 $control_instance   = $attributes[0]->newInstance();
				 	 $control_properties = $control_instance->get_properties();
				 	 if(array_key_exists("required", $control_properties)){
				 	 	unset($control_properties['required']);
				 	 }
				 	 $control_instance->set_properties($control_properties);
				 	 $label = $control_instance->get_label();
				 	 $this->rows[$label]     = new EditRow(
				 		 label:   $label, 
				 		 value:   $source_property ? $source_property.".".$property_name : $property_name, 
				 		 control: $control_instance
				 	 );
				 }
			 }
		}
	 }

	 public function get_dao(){
	 	return $this->dao;
	 }

	 public function get_source_property(){
	 	return $this->source_property;
	 }

	 public function get_title(){
	 	return $this->title;
	 }

	 public function get_rows(){
	 	return $this->rows;
	 }

	 public function set_title(string $title){
	 	$this->title = $title;
	 }

	 public function set_rows(array $rows){
	 	foreach($rows as $r){
	 		$this->rows[$r->get_label()] = $r;
	 	}
	 }

	 public function add_edit_row(EditRow $row){
	 	$this->rows[$row->get_label()] = $row;
	 }

	 /**
	  * Set whether this groups edit rows are editable or not. 
	  * @param bool | Closure
	  * */
	 public function is_editable(Closure | array | bool $is_editable){
	 	 if(is_callable($is_editable) || is_bool($is_editable)){
	 	 	foreach($this->rows as $r){
	 	 		$r->is_editable($is_editable);
	 	 	}
	 	 	$this->editable = is_callable($is_editable) ? $is_editable() : $is_editable;
	 	 }else{
	 	 	foreach($is_editable as $k => $r){
	 	 		$this->rows[$k]->is_editable($r);
	 	 	}
	 	 	$this->editable = true;
	 	 }
	 }

	 public function set_value(array $values){
	 	foreach($values as $l => $v){
	 		$this->rows[$l]->set_value($v);
	 	}
	 }

	 public function set_value_function(array $functions){
	 	foreach($functions as $l => $v){
	 		$this->rows[$l]->set_value_function($v);
	 	}
	 }

	 public function set_display_function(array $functions){
	 	foreach($functions as $l => $v){
	 		$this->rows[$l]->set_display_function($v);
	 	}
	 }

	 public function exclude_rows(array $labels){
	 	foreach($labels as $l){
	 		if(array_key_exists($l, $this->rows)){
	 			unset($this->rows[$l]);
	 		}
	 	}
	 }

	 public function set_control(array $controls){
	 	foreach($controls as $l => $c){
	 		$this->rows[$l]->set_control($c);
	 	}
	 }

	 public function set_label(array $labels){
	 	foreach($labels as $l => $ln){
	 		$this->rows[$l]->set_label($ln);
	 	}
	 }

	 public function include(array $rows = [], string $dao = "", string $source_property = ""){
	 	 if($rows){
	 	 	 $this->rows = array_merge($this->rows, $rows);
	 	 	 return;
	 	 }

	 	 if(!$dao && !$source_property){
	 	 	return;
	 	 }

	 	 $reflector    = new \ReflectionClass($dao);
         $properties   = $reflector->getProperties();
		 foreach($properties as $p){
			 $property_name  = $p->getName();
			 $property_value = $p->getDefaultValue();
			 $attributes     = $p->getAttributes(FormControl::class);
			 if(count($attributes) > 0){
			 	 $control_instance   = $attributes[0]->newInstance();
			 	 $control_properties = $control_instance->get_properties();
			 	 if(array_key_exists("required", $control_properties)){
			 	 	unset($control_properties['required']);
			 	 }
			 	 $control_instance->set_properties($control_properties);
			 	 $label = $control_instance->get_label();
			 	 $this->rows[$label]     = new EditRow(
			 		 label:   $label, 
			 		 value:   $source_property ? $source_property.".".$property_name : $property_name, 
			 		 control: $control_instance
			 	 );
			 }
		 }
	 }

	 public function order(array $labels, bool $fill = true){
	 	$temp_rows  = $this->rows;
	 	$this->rows = [];
	 	foreach($labels as $label){
	 		if(array_key_exists($label, $temp_rows)){
	 			$this->rows[$label] = $temp_rows[$label];
	 			unset($temp_rows[$label]);
	 		}
	 	}
        if($fill){
        	foreach($temp_rows as $rk => $rv){
		 		 $this->rows[$rk] = $rv;
		 	     unset($temp_rows[$rk]);
		 	}
        }
	 }

	 public function get_editable(){
	 	return $this->editable;
	 }
}
?>