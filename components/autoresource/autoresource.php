<?php
namespace SaQle\Components\AutoResource;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\Form;
use SaQle\Core\Ui\Panels\TablePanel;
use SaQle\Http\Request\Request;
use SaQle\Core\Registries\ModelRegistry;
use RuntimeException;

class AutoResource {

      private function create_auto_form(array $props){
     	 $model_and_method = request()->route->model_class;
	 	 if($model_and_method){
	 	 	 [$model_class, $method] = explode("@", $model_and_method);
	 	 	 $long_model_name = ModelRegistry::get_long_model_name($model_class);
	 	 	 if($long_model_name){
	 	 	 	 $model_name_parts = explode(".", $long_model_name);
	 	 	 	 $model_name = count($model_name_parts) === 2 ? $model_name_parts[1] : $model_name_parts[0];
	 	 	 	 $module_name = count($model_name_parts) === 2 ? $model_name_parts[0] : "";
	 	 	 	 $form_name = $module_name ? "{$module_name}.{$model_name}.{$method}" : "{$model_name}.{$method}";

	 	 	 	 return Form::make_from_model($model_class, $method, $model_name, $form_name); 
	 	 	 }
	 	 }else{
	 	 	 $form_name = $props['name'] ?? null;
	 	 	 return $form_name ? Form::make_from_name($form_name) : null;
	 	 }

	 	 return null;
      }

	 public function list_resources(
	 	 int $page = 1,
	 	 int $records = 100
	 ) : Message {

	 	 $panel = new TablePanel(
	 	 	 request()->route->model_class,
	 	 	 ['pagination' => [
	 	 	 	 'page' => $page,
	 	 	 	 'records' => $records
	 	 	 ]]
	 	 );

		 return Message::ok([
		 	'panel' => $panel
		 ]);
	 }

	 public function show_create_form(array $__props) : Message {

	 	 $form = $this->create_auto_form($__props);

	 	 if(!$form){
	 	 	 throw new RuntimeException("Unknown resource form requested!");
	 	 }

		 return Message::ok([
		 	 'form' => $form
		 ]);
	 }

	 public function create_resource() : Message {
		 return Message::ok();
	 }

	 public function show_resource() : Message {
		 return Message::ok();
	 }

	 public function show_edit_form(array $__props) : Message {
		 $form = $this->create_auto_form($__props);

	 	 if(!$form){
	 	 	 throw new RuntimeException("Unknown resource form requested!");
	 	 }

		 return Message::ok([
		 	 'form' => $form
		 ]);
	 }

	 public function edit_resource() : Message {
		 return Message::ok();
	 }

	 public function delete_resource() : Message {
		 return Message::ok();
	 }
}