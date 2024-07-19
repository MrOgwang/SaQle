<?php
namespace SaQle\Controllers\Forms;

use SaQle\Controllers\IController;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;
use SaQle\Views\FormGroup;
use SaQle\Dao\Field\Controls\FormControlCollection;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Http\Response\{HttpMessage, StatusCode};

abstract class FormController extends IController implements FormSetup, Observable{
	 private array  $form_groups      = [];
	 protected string $save_property    = "";
	 private array  $data_mapping     = [];
	 private array  $result_mapping   = [];
	 private array  $data_sources     = [];
	 private array  $observer_classes = [];
	 private string $success_message  = "Save operation completed successfully!";


	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 public function __construct(Request $request, array $context = [], ...$kwargs){
		 $this->__coConstruct();
		 parent::__construct($request, $context, $kwargs);
	 }

	 abstract public function form_setup();
	 public function inject_extra_context(){
	 	return [];
	 }
	 protected function add_form_group(string $title, string $description, FormControlCollection $controls){
	 	$this->form_groups[] = new FormGroup(
	 		title:        $title,
	 	 	description:  $description,
	 	 	controls:     $controls
	 	);
	 }
	 protected function set_save_property($save_property){
	 	$this->save_property = $save_property;
	 }
	 protected function set_data_sources(array $data_sources){
	 	$this->data_sources = $data_sources;
	 }
	 protected function set_observer_classes(array $observer_classes){
	 	$this->observer_classes = $observer_classes;
	 }
	 protected function set_success_message($message){
	 	$this->success_message = $message;
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
	 public function post() : HttpMessage{
	 	 $context = $this->get()->get_response();
	 	 if($this->request->data->get($this->save_property, '')){
	 		 try{
		 		 //attach all observers.
		 		 foreach($this->observer_classes as $observer){
		 		 	$observer_instance = new $observer($this);
		 		 }

			 	 foreach($this->data_sources as $source){
			 	 	$data_source_fields = $source->get_fields();
			 	 	$data_source_aliase = $source->get_aliase();
			 	 	$table_name         = $source->get_table();
			 	 	$multiple           = $source->get_multiple();
			 	 	$data_source_name   = $data_source_aliase ? $table_name.":".$data_source_aliase : $table_name.":".$table_name;
			 	 	if(!array_key_exists($data_source_name, $this->data_mapping)){
			 	 		$this->data_mapping[$data_source_name] = [];
			 	 	}
			 	 	$row_mapping = ['data' => [], 'dependancies' => [], 'multiple' => $multiple];
			 	 	foreach($data_source_fields as $field){
			 	 		$name       = $field->get_name();
			 	 		$source     = $field->get_source();
			 	 		$row_value  = $field->get_value();
			 	 		$is_file    = $field->get_is_file() ? 'file' : 'scalar';

			 	 		$field_key_name = $name.":".$is_file;
			 	 		$value      = match($source){
						    'form'     => $this->request->data->get($field->get_control()),
						    'defined'  => $row_value,
						    'callable' => $row_value(),
						    'result'   => null,
						     default   => throw new \Exception('Unsupported field data source!'),
						};
						$row_mapping['data'][$field_key_name] = $value;
						if(is_null($value) && !is_callable($row_value)){
							$row_mapping['dependancies'][$field_key_name] = $row_value;
						}
			 	 	}
			 	 	$this->data_mapping[$data_source_name][] = (Object)$row_mapping;
			 	 }

			 	 foreach($this->data_mapping as $complex_table_name => $rows){
			 	 	[$table_name, $table_aliase] = explode(":", $complex_table_name);
			 	 	$this->result_mapping[$table_aliase] = [];
			 	 	foreach($rows as $row_object){
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

			 	 		//save the data
			 	 		$modelmanager = $this->context[0]->get($table_name);
			 	 		if($row_object->multiple){
			 	 			$data_to_save = $this->expand_data($row_object->data);
			 	 		}else{
			 	 			$data_to_save = [$this->remove_types_from_data($row_object->data)];
			 	 		}
			 	 		$results = $modelmanager->add_multiple($data_to_save)->save(multiple: true);
			 	 		if($results){
			 	 			foreach($data_to_save as $dtsi => $dts){
			 	 				$array_diff = array_values(array_diff(array_keys($dts), array_keys(get_object_vars($results[$dtsi]))));
				 	 			foreach($array_diff as $d){
				 	 				$results[$dtsi]->$d = $dts[$d];
				 	 			}
				 	 			$this->result_mapping[$table_aliase][] = $results[$dtsi];
			 	 			}
			 	 		}
			 	 	}
			 	 }

			 	 //notify the observers
			     $this->feedback->set(status: FeedBack::SUCCESS, feedback: $this->result_mapping);
			     $this->notify();

			     $context["message"] = "
	 		 	 <div style='margin-bottom: 20px;' class='system-info system-info-success'>
		             {$this->success_message}
		         </div>
	 		 	 ";
	 		 }catch(\Exception $e){
	 		 	$context["message"] = "
	 		 	<div style='margin-bottom: 20px;' class='system-info system-info-danger'>
		             {$e}
		        </div>
	 		 	";
	 		 }
		 }
		 return new HttpMessage(StatusCode::OK, $context);
	 }
	 public function get() : HttpMessage{
	 	$this->form_setup();
	 	$message = "";
	 	if(isset($_SESSION['message'])){
	 		$message = "
	 		<div style='margin-bottom: 20px;' class='system-info system-info-neutral'>
		         " .$_SESSION['message']. "
		    </div>
	 		";
	 		unset($_SESSION['message']);
	 	}
	 	$context = ['message' => $message, 'form_controls' => ''];
	 	foreach($this->form_groups as $group){
	 		$title       = $group->get_title();
	 		$description = $group->get_description();
	 		$controls    = $group->get_controls();
	 		$group_form  = "";
	 		foreach($controls as $c){
	 			 $group_form  .= $c->get_control();
	 		}
	 		$context["form_controls"] .= "
	 		<!---------------->
		     <div class='app-form-group'>
		         <div class='app-form-group-left'>
		             <h3>{$title}</h3>
		             <p>{$description}</p>
		         </div>
		         <div class='app-form-group-right'>
		            {$group_form}
		         </div>
		     </div>
		     <!------------------>
	 		";
	 	}
	 	$context = array_merge($context, $this->inject_extra_context());
	 	return new HttpMessage(StatusCode::OK, $context);
	 }
}
?>