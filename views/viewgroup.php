<?php
namespace SaQle\Views;

use SaQle\Orm\Entities\Field\Controls\FormControl;
use SaQle\Orm\Entities\Field\Attributes\{PrimaryKey, ForeignKey, NavigationKey};
use SaQle\Orm\Database\DbContext;
use SaQle\Views\ViewGroupSettings;
use SaQle\Commons\StringUtils;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use Closure;
use stdClass;

class ViewGroup{
	use StringUtils;
	/**
	 * The title of group
	 * @var string
	 */
	 private string $title;

	 /**
	 * The table name associated with the data access object class
	 * @var string
	 */
	 private string $table;

	 /**
	 * Edit rows
	 * @var array
	 */
	 private array $rows = [];

	 /**
	  * Is editable : set the rows of this group as editable.
	  * @var boolean
	  * */
	 private bool $editable = true;

	 /**
	  * Is deletable : set this view group as deletable
	  * @var boolean
	  * */
	 private bool $deletable = false;

	 /**
	  * Primary field for the object displayed by this view group
	  * @var string
	  * */
	 private string $primary_field = "";

	 private string $type = "";
	 
	 /**
	 * Create a new form group instance
	 * @param string          $title
	 * @param string | IModel $dao
	 * @param DbContext       $context: The database context
	 * @param string          $sproperty: the source property of the sdata object, defaults to empty string
	 * @param stdClass        $sdata: the source data object, defaults to null
	 * @param int             $gindex: the view group index in the collection
	 */
	 public function __construct(string $title, string | IModel $dao, DbContext $context, ?stdClass $sdata = null, int $gindex = -1, array $relations = []){
	 	 $this->primary_field   = is_string($dao) ? (new $dao())->get_pk_name() : $dao->get_pk_name();
		 $this->title           = $title;
		 $this->table           = $context->get_dao_table_name(is_string($dao) ? $dao : $dao::class);
		 $this->rows            = $this->extract_view_rows_from_dao(is_string($dao) ? new $dao() : $dao, $context, $sdata, $sdata, $gindex, $relations);
	 }

	 public function set_type(string $type){
	 	$this->type = $type;
	 }

	 public function get_type(){
	 	return $this->type;
	 }

	 private function create_child_group($title, $dao, $context, $sdata, $gindex, ?array $settings = null, array $relations = []){
	 	 $child_view_group = new ViewGroup(
 	 	 	 title:     $title, 
 	 	 	 dao:       $dao, 
 	 	 	 context:   $context,  
 	 	 	 sdata:     $sdata,
 	 	 	 gindex:    $gindex,
 	 	 	 relations: $relations
 	 	 );
 	 	 $child_view_group->is_deletable(true);
 	 	 $child_view_group->is_editable(true);
 	 	 if($settings){
 	 	 	 [$title, $labels, $exclude, $edit, $controls, $defaults] = $settings;
 	 	 	 $child_view_group->set_label($labels);
		 	 $child_view_group->exclude_rows($exclude);
		 	 $child_view_group->is_editable($edit);
		 	 $child_view_group->set_control($this->get_control_from_settings($controls, $gindex));
		 	 $child_view_group->show_value($defaults);
 	 	 }
 	 	 return $child_view_group;
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

	 public function add_view_row(ViewRow $row){
	 	$this->rows[$row->get_label()] = $row;
	 }

	 /**
	  * Set whether this groups edit rows are editable or not. 
	  * @param bool | Closure
	  * */
	 public function is_editable(Closure | array | bool $is_editable, ?array &$rows = null){
	 	 if(is_array($is_editable)){
	 	 	 if($rows){
	 	 		 foreach($is_editable as $k => $r){
		 	 		 if(array_key_exists($k, $rows)){
		 	 			 $rows[$k]->is_editable($r);
		 	 		 }
		 	 	 }
		 	 	 return;
	 	 	 }

             foreach($is_editable as $k => $r){
	 	 		if(array_key_exists($k, $this->rows)){
	 	 			$this->rows[$k]->is_editable($r);
	 	 		}
	 	 	 }
	 	 	 $this->editable = true;
	 	 	 return;
	 	 }

	 	 if($rows){
	 	 	 foreach($rows as $r){
	 	 		 $r->is_editable($is_editable);
	 	 	 }
	 	 	 return;
	 	 }

	 	 foreach($this->rows as $r){
 	 		$r->is_editable($is_editable);
 	 	 }
 	 	 $this->editable = is_callable($is_editable) ? $is_editable() : $is_editable;
 	 	 return;
	 }

	 /**
	  * Set whether this group view is deletable
	  * @param bool 
	  * */
	 public function is_deletable(bool $deletable){
	 	$this->deletable = $deletable;
	 }

	 public function set_value(array $values){
	 	foreach($values as $l => $v){
	 		if(array_key_exists($l, $this->rows)){
	 			$this->rows[$l]->set_value($v);
	 		}
	 	}
	 }

	 public function set_value_function(array $functions){
	 	foreach($functions as $l => $v){
	 		if(array_key_exists($l, $this->rows)){
	 			$this->rows[$l]->set_value_function($v);
	 		}
	 	}
	 }

	 public function set_display_function(array $functions){
	 	foreach($functions as $l => $v){
	 		if(array_key_exists($l, $this->rows)){
	 			$this->rows[$l]->set_display_function($v);
	 		}
	 	}
	 }

	 public function exclude_rows(array $labels, ?array &$rows = null){
	 	if($rows){
	 		foreach($labels as $l){
		 		if(array_key_exists($l, $rows)){
		 			unset($rows[$l]);
		 		}
		 	}
	 	}else{
	 		foreach($labels as $l){
		 		if(array_key_exists($l, $this->rows)){
		 			unset($this->rows[$l]);
		 		}
		 	}
	 	}
	 }

	 public function set_control(array $controls, ?array &$rows = null){
	 	if($rows){
	 		foreach($controls as $l => $c){
		 		 if(array_key_exists($l, $rows)){
		 		 	$rows[$l]->set_control($c);
		 		 }
		 	}
		 	return;
	 	}

	 	foreach($controls as $l => $c){
	 		 if(array_key_exists($l, $this->rows)){
	 		 	$this->rows[$l]->set_control($c);
	 		 }
	 	}
	 }

	 public function show_value(bool $show_value, ?array &$rows = null){
	 	if($rows){
	 		foreach($rows as $r){
	 			$r->set_show_value($show_value);
	 		}
		 	return;
	 	}

	 	foreach($this->rows as $r){
 			$r->set_show_value($show_value);
 		}
	 	return;
	 }

	 public function set_label(array $labels, ?array &$rows = null){
	 	if($rows){
	 		foreach($labels as $l => $ln){
		 		 if(array_key_exists($l, $rows)){
		 		 	$rows[$l]->set_label($ln);
		 		 }
		 	}
	 	}else{
	 		foreach($labels as $l => $ln){
		 		 if(array_key_exists($l, $this->rows)){
		 		 	$this->rows[$l]->set_label($ln);
		 		 }
		 	}
	 	}
	 }

     private function extract_view_group_settings($field, $row_label){
     	 $attr     = $field->getAttributes(ViewGroupSettings::class);
 	     $title    = "New ".$row_label;
 	     $labels   = [];
 	     $exclude  = [];
 	     $edit     = true;
 	     $controls = [];
 	     $defaults = false;
	 	 if($attr){
	 	 	 $vg_settings = $attr[0]->newInstance();
	 	 	 $title       = $vg_settings->get_title() ?? $title;
	 	 	 $labels      = $vg_settings->get_labels() ?? $labels;
	 	 	 $exclude     = $vg_settings->get_exclude() ?? $exclude;
	 	 	 $edit        = $vg_settings->get_edit();
	 	 	 $controls    = $vg_settings->get_controls() ?? $controls;
	 	 	 $defaults    = $vg_settings->get_defaults() ?? $defaults;
	 	 }
	 	 return [$title, $labels, $exclude, $edit, $controls, $defaults];
     }

     private function get_control_from_settings(array $ctrl_settings, $gindex = -1){
     	$controls = [];
     	foreach($ctrl_settings as $label => $settings){
     		if(array_key_exists("name", $settings) && $gindex !== -1){
     			$settings['name'] = $settings['name']."_".$gindex;
     		}
     		$controls[$label] = new FormControl(...$settings);
     	}
     	return $controls;
     }

     private function extract_view_rows_from_dao(IModel $dao, $context, $sdata, $del_data, $gindex, $relations){
     	 $reflector          = new \ReflectionClass($dao);
         $properties         = $reflector->getProperties();
         $rows               = [];
		 foreach($properties as $p){
			 $property_name  = $p->getName();
			 $attributes     = $p->getAttributes(FormControl::class);
			 if(count($attributes) > 0){
			 	 $constructed_view_row = $this->construct_view_row_from_control($attributes[0], $property_name, $sdata, $del_data, $gindex);
			 	 $rows                 = array_merge($rows, $constructed_view_row);
			 }

			 /*If this is a navigation field or a foreign key field, nest or merge the foreign data access object*/
			 $nav_attributes = array_merge($p->getAttributes(NavigationKey::class), $p->getAttributes(ForeignKey::class));
		     if(!$nav_attributes)
		 	     continue;

			 $fn_key_instance = $nav_attributes[0]->newInstance();

			 if(!$fn_key_instance->get_include())
			 	continue;

	         $fmodel             = $fn_key_instance->fmodel;
	         $fn_sproperty     = $fn_key_instance->get_field();
	         $row_label        = ucwords(str_replace("_", " ", $fn_sproperty));
		 	 [$title, $labels, $exclude, $edit, $controls, $defaults] = $this->extract_view_group_settings($p, $row_label);

		 	 if(!$fn_key_instance->get_multiple()){
		 	 	$rows = array_merge($rows, $this->extract_view_rows_from_dao(new $fmodel(), $context, $sdata ? $sdata->$fn_sproperty : $sdata, $sdata, $gindex, $relations));
		 	 	$this->set_label($labels, $rows);
		 	 	$this->exclude_rows($exclude, $rows);
		 	 	$this->is_editable($edit, $rows);
		 	 	if(!$sdata){
		 	 		$this->set_control($this->get_control_from_settings($controls, $gindex), $rows);
		 	 	}
		 	 	continue;
		 	 }

		 	 /*The sdata must be available from this point onwards*/
		 	 if(!$sdata)
		 	 	continue;

		 	 $name_property   = (new $fmodel())->get_name_property();
	 	     $children        = $sdata->$fn_sproperty;
	 	 	 $group_control   = new ViewGroupCollection();
	 	 	 foreach($children as $cindex => $child){
	 	         $group_control->add_group($this->create_child_group(
	 	         	 title:     self::get_property_value($name_property, $child) ?? '', 
		 	 	 	 dao:       $fmodel, 
		 	 	 	 context:   $context, 
		 	 	 	 sdata:     $child,
		 	 	 	 gindex:    $cindex,
		 	 	 	 settings:  [$title, $labels, $exclude, $edit, $controls, $defaults],
		 	 	 	 relations: $relations
	 	         ));
		 	 }
		 	 //new view group
		 	 $new_view_group   = $this->create_child_group(
		 	 	 title:     $title, 
		 	 	 dao:       $fmodel, 
		 	 	 context:   $context,
		 	 	 sdata:     null, 
		 	 	 gindex:    count($children),
		 	 	 settings:  [$title, $labels, $exclude, $edit, $controls, $defaults],
		 	 	 relations: $relations
		 	 );
		 	 $new_view_group->set_type("FormGroup");
 	         $group_control->add_group($new_view_group);
 	         $rows[$row_label] = new ViewRow(label: $row_label, value: $property_name, control: $group_control);
		 }
		 return $rows;
     }

     private function construct_view_row_from_control($ctrl_attr, $property_name, $sdata, $del_data, $gindex){
     	 $control_instance   = $ctrl_attr->newInstance();
	 	 $control_properties = $control_instance->get_properties();
	 	 if($gindex != -1){
	 	 	 $control_instance->set_name($control_instance->get_name()."_".$gindex);
	 	 }
	 	 if(array_key_exists("required", $control_properties)){
	 	 	unset($control_properties['required']);
	 	 }
	 	 $control_instance->set_properties($control_properties);
	 	 $label = $control_instance->get_label();
	 	 return [$label => new ViewRow(
	 		 label:    $label, 
	 		 value:    $property_name, 
	 		 control:  $control_instance,
	 		 sdata:    $sdata,
	 		 del_data: $del_data
	 	 )];
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

	 public function get_deletable(){
	 	return $this->deletable;
	 }

	 public function get_primary_field(){
	 	return $this->primary_field;
	 }

	 public function construct_group($source_object, $view_form_id = "", $delete_form_id = ""){
	 	 $group_form  = "";
	 	 $del_data    = null;
	 	 $rowcount    = 0;
		 foreach($this->rows as $r){
 			 $group_form .= $r->get_row($view_form_id, $delete_form_id);
 			 if($rowcount === 0){
 			 	$del_data = $r->get_del_data();
 			 }
 			 $rowcount++;
 		 }
 		 $deletable = "";
 		 if($this->deletable && $del_data){
 		 	$primary_field_name  = $this->primary_field;
 		 	$primary_field_value = "";
 		 	$primary_field_value = $this->table.":".$del_data->$primary_field_name;
 		 	$del_form            = $delete_form_id ? "form='{$delete_form_id}'" : "";
 		 	$deletable = "
 		 	<div style='margin-left: 20px;'>
                <input {$del_form} value='{$primary_field_value}' type='checkbox' name='mark_delete[]' id='mark_delete-{$primary_field_value}' class='form_input_check'>
                <label for='mark_delete-{$primary_field_value}'>Mark for deletion</label>
            </div>";
 		 }
	     return "
 		 <!---------------->
 		 <div class='view-group'>
		     <div class='flex v_center view-group-header'>
		         <div class='flex v_center view-group-header-title'>
		             <h3>{$this->title}</h3>
		             {$deletable}
		         </div>
		         <div class='collapseeditgroup flex v_center row_reverse view-group-header-collapse'>
		             <span><i data-lucide='chevron-down'></i></span>
		         </div>
		     </div>
		     <div class='view-group-body'>
		         {$group_form}
		     </div>
		 </div>
	     <!------------------>
 		";
	 }
}
?>