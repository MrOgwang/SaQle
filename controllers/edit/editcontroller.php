<?php
namespace SaQle\Controllers\Edit;

use SaQle\Controllers\IController;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;
use SaQle\Views\EditGroup;
use SaQle\Dao\Field\Controls\FormControlCollection;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

abstract class EditController extends IController implements EditSetup, Observable{
	 private array  $edit_groups      = [];
	 private string $edit_property    = "";
	 private string $edit_title       = "";
	 private array  $data_mapping     = [];
	 private array  $result_mapping   = [];
	 private array  $data_sources     = [];
	 private        $object_data;
	 private array  $observer_classes = [];
	 //private        $context;
	 private string $database_context_class;
	 private string $success_message  = "Edit operation completed successfully!";
	 private bool   $is_editable      = false;
	 private bool   $is_deletable     = false;

	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 public function __construct(Request $request){
		 $this->__coConstruct();
		 parent::__construct($request);
	 }

	 abstract public function edit_setup();
	 public function inject_extra_context(){
	 	return [];
	 }
	 protected function set_edit_title($title){
	 	$this->edit_title = $title;
	 }
	 protected function set_is_editable(bool $editable){
	 	$this->is_editable = $editable;
	 }
	 protected function set_is_deletable(bool $deletable){
	 	$this->is_deletable = $deletable;
	 }
	 protected function add_edit_group(string $title, string $dao, string $source_property = "", array $rows = []){
	 	$this->edit_groups[] = new EditGroup(title: $title, dao: $dao, source_property: $source_property, rows: $rows);
	 }
	 protected function add_edit_group_main(EditGroup $group){
	 	$this->edit_groups[] = $group;
	 }
	 protected function set_edit_property($edit_property){
	 	$this->edit_property = $edit_property;
	 }
	 protected function set_data_sources(array $data_sources){
	 	$this->data_sources = $data_sources;
	 }
	 protected function set_database_context_class(string $database_context_class){
	 	$this->database_context_class = $database_context_class;
	 }
	 protected function set_observer_classes(array $observer_classes){
	 	$this->observer_classes = $observer_classes;
	 }
	 protected function set_success_message($message){
	 	$this->success_message = $message;
	 }
	 protected function set_object_data($data){
	 	$this->object_data = $data;
	 }
	 abstract protected function get_object_data();
	 private function initialize_context(){
	     $this->context = Cf::create(ContainerService::class)->createDbContext($this->database_context_class);
	 }
	 private function expand_data(array $data){
	 	 $newdata = [];
	 	 $counts  = [];
		 foreach($data as $col => $col_val){
		 	[$col_name, $col_type] = explode(":", $col);
		 	if( ($col_type == 'scalar' && !is_array($col_val)) || $col_type == 'file'){
		 		$col_val = [$col_val];
		 	}
		 	$newdata[$col_name] = $col_val;
		 	$counts[]           = count($col_val);
		 }
		 $max_count     = max($counts);
		 foreach($newdata as $col2 => $col_val2){
		 	if(count($col_val2) < $max_count){
		 		$remainder = $max_count - count($col_val2);
		 		$fill_values = [];
		 		for($r = 0; $r < $remainder; $r++){
		 			$fill_values[] = $col_val2[ count($col_val2) -1 ];
		 		}
		 		$newdata[$col2] = array_merge($col_val2, $fill_values);
		 	}
		 }

		 $newdata_keys = array_keys($newdata);
		 $newdata_values = array_values($newdata);
		 $expanded_data = [];
		 for($c = 0; $c < $max_count; $c++){
		 	$rowdata = [];
		 	foreach($newdata_keys as $ndki => $ndk){
		 		$rowdata[$ndk] = $newdata_values[$ndki][$c];
		 	}
		 	$expanded_data[] = $rowdata;
		 }

		 return $expanded_data;
	 }
	 private function remove_types_from_data(array $data){
	 	 $newdata = [];
		 foreach($data as $col => $col_val){
		 	[$col_name, $col_type] = explode(":", $col);
		 	$newdata[$col_name] = $col_val;
		 }
		 return $newdata;
	 }
	 private function walk_get_value(string $property_name, $object){
	 	$properties = explode(".", $property_name);
	 	$value      = $object;
	 	if(is_null($value)){
	 		return $value;
	 	}
	 	for($p = 0; $p < count($properties); $p++){
	 		 $cp = $properties[$p];
	 		 $value = is_numeric($cp) ? $value[$cp] : $value->$cp;
	 		 if(is_null($value)){
	 		 	break;
	 		 }
	 	}
	 	return $value;
	 }
	 public function post() : HttpMessage{
	 	 $context      = $this->get()->get_response();
	 	 $message      = "";
	 	 if($this->request->data->get($this->edit_property, '')){
	 		 try{
	 		 	//initialize database context
		 		 $this->initialize_context();
		 		 //attach all observers.
		 		 foreach($this->observer_classes as $observer){
		 		 	$observer_instance = new $observer($this);
		 		 }
			 	 foreach($this->data_sources as $source_index => $source){
			 	 	 $data_source_fields = $source->get_fields();
			 	 	 $data_source_aliase = $source->get_aliase();
			 	 	 $table_name         = $source->get_table();
			 	 	 $multiple           = $source->get_multiple();
			 	 	 $primary            = $source->get_primary();
			 	 	 [$primary_key_name, $primary_key_source] = explode("=", $primary);
			 	 	 $primary_key_value  = $this->walk_get_value($primary_key_source, $this->object_data);
			 	 	 $action             = $primary_key_value ? "edit" : "add";
			 	 	 $data_source_name   = $source->get_datasource_name();

                     $row_mapping = ['data' => [], 'dependancies' => [], 'multiple' => $multiple, 'action' => $action, 
			 	 	               "primary_key_name" => $primary_key_name, "primary_key_value" => $primary_key_value];
			 	 	 foreach($data_source_fields as $field){
			 	 		$name       = $field->get_name();
			 	 		$source     = $field->get_source();
			 	 		$row_value  = $field->get_value();
			 	 		$is_file    = $field->get_is_file() ? 'file' : 'scalar';

			 	 		$field_key_name = $name.":".$is_file;
			 	 		/**
			 	 		 * Only collect the fields whose source is form and whose values exists.
			 	 		 * */
			 	 		 if($source == "form" || $source == "result"){
			 	 			 $value = match($source){
						         'form'   => $this->request->data->get($field->get_control(), ''),
						         'result' => null,
						     };
						     $value = $is_file == "file" && $value['error'] != UPLOAD_ERR_OK ? '' : $value;
			 	 			 if($value){
			 	 			 	 $row_mapping['data'][$field_key_name] = $value;
			 	 			 }
			 	 			 if(is_null($value) && !is_callable($row_value)){

			 	 			 	 /*$dmapped_keys = array_keys($this->data_mapping);
			 	 			 	 [$dependancy_table, $dependancy_table_column] = explode(":", $row_value);
			 	 			 	 for($s = 0; $s < count($dmapped_keys); $s++){
			 	 			 	 	 
			 	 			 	 	 $prev_primary_key_name  = $this->data_mapping[$dmapped_keys[$s]][0]->primary_key_name;
			 	 			 	 	 $prev_primary_key_value = $this->data_mapping[$dmapped_keys[$s]][0]->primary_key_value;
			 	 			 	 	 
			 	 			 	 	 if( str_contains($dmapped_keys[$s], $dependancy_table) && $prev_primary_key_name == $dependancy_table_column){
			 	 			 	 		 $row_mapping['data'][$field_key_name] = $prev_primary_key_value;
			 	 			 	 		 break;
			 	 			 	 	 }
			 	 			 	 }*/

							     $row_mapping['dependancies'][$field_key_name] = $row_value;

						     }
			 	 		}
			 	 	 }
                     if(!array_key_exists($data_source_name, $this->data_mapping)){
		 	 		     $this->data_mapping[$data_source_name] = [];
		 	 	     }

                 	 $this->data_mapping[$data_source_name][] = (Object)$row_mapping;
			 	 }

			 	 foreach($this->data_mapping as $complex_table_name => $rows){
			 	 	[$table_name, $table_aliase] = explode(":", $complex_table_name);
			 	 	$this->result_mapping[$table_aliase] = [];
			 	 	foreach($rows as $row_object){
			 	 		//only deal with row_object with data.
			 	 		$results        = [];
			 	 		$modelmanager   = $this->context->get($table_name);
			 	 		$data_to_save   = null;
			 	 		if($row_object->data){
			 	 			 if(count($row_object->dependancies) > 0){
				 	 			 foreach($row_object->dependancies as $dn => $dv){
				 	 				 [$table, $source_field] = explode(":", $dv);
				 	 				 [$dn_col, $col_type] = explode(":", $dn);
				 	 				 if( !array_key_exists($table, $this->result_mapping) || count($this->result_mapping[$table]) === 0 ){
				 	 				 	 throw new \Exception('Data source dependancy does not exist!');
				 	 				 }
				 	 				 $row_object->data[$dn] = $this->result_mapping[$table][0]->$source_field;
				 	 			 }
				 	 		 }
				 	 		 if($row_object->primary_key_value){
				 	 		 	$row_object->data[$primary_key_name.":scalar"] = $row_object->primary_key_value;
				 	 		 }
			 	 			 if($row_object->multiple){
				 	 			 $data_to_save = $this->expand_data($row_object->data);
				 	 		 }else{
				 	 			 $data_to_save = [$this->remove_types_from_data($row_object->data)];
				 	 		 }

			 	 			 if($row_object->action == "add"){
			 	 			 	 $results = $modelmanager->add_multiple($data_to_save)->save(multiple: true);
			 	 			 }else{
			 	 			 	 //print_r($data_to_save);
			 	 			 	 // print_r($row_object);
			 	 			 	 $results = $modelmanager->set_multiple($data_to_save)
			 	 			 	 ->where($row_object->primary_key_name.'__eq', $row_object->primary_key_value)->update();
			 	 			 }
			 	 		}else{
			 	 			 $results = $row_object->primary_key_value ?
			 	 			 $modelmanager->where($row_object->primary_key_name.'__eq', $row_object->primary_key_value)->all() : $results;
			 	 		}

			 	 		if($results){
			 	 			if($data_to_save){
			 	 				foreach($data_to_save as $dtsi => $dts){
				 	 				$array_diff = array_values(array_diff(array_keys($dts), array_keys(get_object_vars($results[$dtsi]))));
					 	 			foreach($array_diff as $d){
					 	 				$results[$dtsi]->$d = $dts[$d];
					 	 			}
					 	 			$this->result_mapping[$table_aliase][] = $results[$dtsi];
				 	 			}
			 	 			}else{
			 	 				$this->result_mapping[$table_aliase] = array_merge($this->result_mapping[$table_aliase], $results);
			 	 			}
			 	 		}
			 	 	}
			 	 }

			 	 //notify the observers
			     $this->feedback->set(status: FeedBack::SUCCESS, feedback: $this->result_mapping);
			     $this->notify();

			     $message = "
	 		 	 <div style='margin-bottom: 20px;' class='system-info system-info-success'>
		             {$this->success_message}
		         </div>
	 		 	 ";
	 		 	 //header('Location: '.$_SERVER['REQUEST_URI']);
	 		 }catch(\Exception $e){
	 		 	$message = "
	 		 	<div style='margin-bottom: 20px;' class='system-info system-info-danger'>
		             {$e}
		        </div>
	 		 	";
	 		 }
		 }
		 $context['message'] = $message;
		 return new HttpMessage(StatusCode::OK, $context);
	 }
	 public function get() : HttpMessage{
	 	$this->edit_setup();
	 	$context   = ['message' => '', 'edit_controls' => '', 'edit_title' => $this->edit_title];
	 	foreach($this->edit_groups as $group){
	 		 if($group->get_editable()){
	 		 	$this->is_editable = true;
	 		 }
	 		 $title       = $group->get_title();
	 		 $rows        = $group->get_rows();
	 		 $group_form  = "";
 		 	 foreach($rows as $r){
	 			 $group_form .= $r->get_row($this->object_data);
	 		 }
	 		 $context["edit_controls"] .= "
	 		 <!---------------->
	 		 <div class='view-group'>
			     <div class='collapseeditgroup flex v_center view-group-header'>
			         <div class='view-group-header-title'>
			             <h3>{$title}</h3>
			         </div>
			         <div class='flex v_center row_reverse view-group-header-collapse'>
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

	 	$context['editoff'] = $this->is_editable ? "" : "hide";
	 	$context = array_merge($context, $this->inject_extra_context());
	 	return new HttpMessage(StatusCode::OK, $context);
	 }
}
?>