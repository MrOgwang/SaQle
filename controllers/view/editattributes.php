<?php
namespace SaQle\Controllers\View;
use SaQle\Http\Request\Request;
use SaQle\FeedBack\FeedBack;

class EditAttributes{
	 /**
	  * When the form is submitted and the request data object contains this property, thats a signal to save edited changes.
	  * @var string
	  * */
	 public string $edit_property    = "edits_save_signal";
	 public bool   $editable         = false;
	 public array  $result_mapping   = [];
	 public array  $observer_classes = [];
	 public string $success_message  = "Edit operation completed successfully!";

     
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

	 public function process(ViewController $controller) : array{
	 	 $request     = $controller->get_request();
	 	 $message     = "";
	 	 if($request->data->get($this->edit_property, '')){
	 		 try{
		 		 $context = $controller->get_dbcontext();
		 		 //attach all observers.
		 		 foreach($this->observer_classes as $observer){
		 		 	$observer_instance = new $observer($controller);
		 		 }

		 		 $data_mapping = $controller->get_data_mapping();
		 		 //print_r($data_mapping);
			 	 foreach($data_mapping as $complex_table_name => $rows){
			 	 	[$table_name, $table_aliase] = explode(":", $complex_table_name);
			 	 	$this->result_mapping[$table_aliase] = [];
			 	 	foreach($rows as $row_object){
			 	 		//only deal with row_object with data.
			 	 		$results        = [];
			 	 		$modelmanager   = $context->get($table_name);
			 	 		$data_to_save   = null;
			 	 		if($row_object->data){
				 	 		 if($row_object->primary_key_value){
				 	 		 	$primary_key_name = $row_object->primary_key_name;
				 	 		 	$row_object->data[$primary_key_name.":scalar"] = $row_object->primary_key_value;
				 	 		 }

				 	 		 $data_to_save = array_merge($row_object->data, $row_object->defined);
				 	 		 $data_to_save = $row_object->multiple ? $this->expand_data($data_to_save) : 
				 	 		 [$this->remove_types_from_data($data_to_save)];

				 	 		 //print_r($data_to_save);

				 	 		 $results = $row_object->action == "add" ? $modelmanager->add_multiple($data_to_save)->save(multiple: true) :
				 	 		 $modelmanager->set_multiple($data_to_save)
				 	 		 ->where($row_object->primary_key_name.'__eq', $row_object->primary_key_value)->update();
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
			     $controller->get_feedback()->set(status: FeedBack::SUCCESS, feedback: $this->result_mapping);
			     $controller->notify();

			     $_SESSION['viewcontroller_message'] = "
	 		 	 <div style='margin-bottom: 20px;' class='system-info system-info-success'>
		             {$this->success_message}
		         </div>
	 		 	 ";

	 		 	 $controller->reload();
	 		 	 
	 		 }catch(\Exception $e){
	 		 	$message = "
	 		 	<div style='margin-bottom: 20px;' class='system-info system-info-danger'>
		             {$e}
		        </div>
	 		 	";
	 		 }
		 }
		 return ['message' => $message];
	 }
}
?>