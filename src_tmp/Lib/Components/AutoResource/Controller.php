<?php

namespace SaQle\Lib\Components\AutoResource;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\FormField;
use SaQle\Core\Support\Route;
use SaQle\Core\Registries\ModelRegistry;
use RuntimeException;
use Throwable;

class Controller {

     private function acquire_auto_form(string $model, string $form_name){
     	 $model_class = ModelRegistry::all()[$model] ?? null;
	 	 if(!$model_class){
	 	 	 throw new RuntimeException('Invalid request!');
	 	 }

	 	 $form_def = $model_class::get_forms_definition();
     	 $form = $form_def->forms[$form_name] ?? null;

     	 if(!$form){
     	 	 throw new RuntimeException('Invalid request!');
     	 }

     	 return [$model_class, $form];
     }

     private function load_select_choices(
     	 ?FormField $form_field = null,
     	 null|array|string $scope_col_name = null,
     	 mixed $scope_col_val  = null,
     	 bool $search = false
     ) : array {
     	 
     	 if(!$form_field){
     	 	 throw new RuntimeException('Invalid request!');
     	 }

     	 $source = $form_field->get_source();
     	 $related_model = $source['related_model'];
     	 $query_callback = $source['query'] ?? null;

     	 $name_property = $related_model::get_name_property() ?? [];
         if($name_property){ 
             $name_property = is_array($name_property) ? $name_property : [$name_property];
         }
         $pk_name = $related_model::get_pk_name();

         $select_columns = [$pk_name]; 
         if($name_property){
             $select_columns = array_merge($select_columns, $name_property);
         }

         $query = $related_model::get()->select($select_columns);
         if($scope_col_name && $scope_col_val){

         	 $operator = $search ? "contains" : "eq";

         	 if(is_string($scope_col_name)){
         	 	 $query->where($scope_col_name."__".$operator, $scope_col_val);
         	 }else{

         	 	 $first_col = array_shift($scope_col_name); 
         	 	 $query->where($first_col."__".$operator, $scope_col_val);

         	 	 foreach($scope_col_name as $cn){
         	 	 	 $query->or_where($cn."__".$operator, $scope_col_val);
         	 	 }
         	 }
         }
         if($query_callback){
         	 $query = $query_callback($query);
         }

         $data = $query->all();

         $choices = [];

         foreach($data as $d){
             $choices[$d->$pk_name] = $name_property ? (string)$d : $d->$pk_name;
         }

         return $choices;
     }

     #[Route(
         name: 'select.options',
         method: 'get', 
         url: '/forms/options', 
     )]
	 public function select_field_options(
	 	 string $fname, 
	 	 string $model,
	 	 string $field
	 ){
	 	 try{

	 	 	 [$model_class, $form] = $this->acquire_auto_form($model, $fname);

	 	 	 $choices = $this->load_select_choices($form->field($field));

	         return Message::ok($choices);

	 	 }catch(Throwable $e){
	 	 	 return Message::bad_request(message: 'There was an error loading options!');
	 	 }
	 }

	 #[Route(
         name: 'select.cascade.options',
         method: 'get', 
         url: '/forms/options/cascade', 
     )]
	 public function select_cascade_options(
	 	 string $fname, 
	 	 string $model,
	 	 string $field,
	 	 string $cfields,
	 	 mixed  $val = null
	 ){
	 	 try{

	 	 	 [$model_class, $form] = $this->acquire_auto_form($model, $fname);

	 	 	 $field_names = explode(",", $cfields);

	 	 	 $choices = [];

	 	 	 $col_name = $model_class::$field()->get_column();

	 	 	 foreach($field_names as $n){
	 	 	 	 $choices[$n] = $this->load_select_choices($form->field($n), $col_name, $val);
	 	 	 }

	         return Message::ok($choices);

	 	 }catch(Throwable $e){
	 	 	 return Message::bad_request(message: 'There was an error loading options!');
	 	 }
	 }

	 #[Route(
         name: 'select.search.options',
         method: 'get', 
         url: '/forms/options/search', 
     )]
	 public function select_search_options(
	 	 string $fname, 
	 	 string $model,
	 	 string $field,
	 	 string $keyword
	 ){
	 	 try{

	 	 	 [$model_class, $form] = $this->acquire_auto_form($model, $fname);

	 	 	 $form_field = $form->field($field);

	 	 	 $choices = $this->load_select_choices($form_field, $form_field->searchable, $keyword, true);

	         return Message::ok($choices);

	 	 }catch(Throwable $e){
	 	 	 return Message::bad_request(message: 'There was an error loading options!');
	 	 }
	 }
}