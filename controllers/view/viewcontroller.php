<?php
namespace SaQle\Controllers\View;

use SaQle\Controllers\Base\BaseController;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;
use SaQle\Views\ViewGroup;
use SaQle\Orm\Entities\Field\Controls\FormControlCollection;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Commons\StringUtils;

abstract class ViewController extends BaseController implements ViewSetup, Observable{
      use StringUtils;
	 private ?ViewAttributes   $view_attributes   = null;
	 private ?EditAttributes   $edit_attributes   = null;
	 private ?DeleteAttributes $delete_attributes = null;
	 private                   $object_data       = null;
	 private                   $dbcontext         = null;
	 private array             $data_mapping      = [];
	 private array             $data_sources      = [];

	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 public function __construct(Request $request, array $context = [], ...$kwargs){
		 $this->__coConstruct();
		 parent::__construct($request, $context, $kwargs);

		 $this->view_attributes   = new ViewAttributes();
		 $this->edit_attributes   = new EditAttributes();
		 $this->delete_attributes = new DeleteAttributes();
	 }

	 /**
	  * Override these methods in the child classes
	  * */
	 abstract public function view_setup();
	 public function inject_extra_context(){
	 	return [];
	 }

     /**
      * Get edit attributes
      * */
	 public function get_edit_attributes(){
	 	return $this->edit_attributes;
	 }

	 /**
	  * Get delete attributes
	  * */
	 public function get_delete_attributes(){
	 	return $this->delete_attributes;
	 }

	 /**
	  * Get view attributes
	  * */
	 public function get_view_attributes(){
	 	return $this->view_attributes;
	 }

	 /**
	  * Get the object being viewed
	  * */
	 public function get_object_data(){
	 	return $this->object_data;
	 }


	 /**
	  * Set the view title
	  * */
	 public function title(string $title){
	 	$this->view_attributes->title = $title;
	 }

	 /**
	  * Add view group properties
	  * */
	 protected function add_view_group(string $title, string $dao, string $source_property = "", array $rows = []){
	 	$this->view_attributes->view_groups->add_group(new ViewGroup(title: $title, dao: $dao, source_property: $source_property, rows: $rows));
	 }

	 /**
	  * Add a view group object
	  * */
	 protected function add_view_group_main(ViewGroup $group){
	 	$this->view_attributes->view_groups->add_group($group);
	 }
     
     /**
      * Can the object being viewed be edited
      * */
     protected function is_editable(bool $editable){
	 	$this->edit_attributes->editable = $editable;
	 }

	 /**
	  * Can the object being viewed be deleted
	  * */
	 protected function is_deletable(bool $deletable){
	 	$this->delete_attributes->deletable = $deletable;
	 }

	 /**
	  * Set delete operation success message
	  * */
	 protected function delete_success_message(string $message){
	 	$this->delete_attributes->success_message = $message;
	 }

	 /**
	  * Set edit operation success message
	  * */
	 protected function edit_success_message(string $message){
	 	$this->edit_attributes->success_message = $message;
	 }

     /**
      * Set edit property
      * */
	 protected function edit_property($edit_property){
	 	 $this->edit_attributes->edit_property = $edit_property;
	 }

	 /**
      * Set delete property
      * */
	 protected function delete_property($delete_property){
	 	 $this->delete_attributes->delete_property = $delete_property;
	 }

     /**
      * Set database context
      * */
	 public function set_dbcontext($context){
	 	 $this->dbcontext = $context;
	 }

	 /**
      * Get database context
      * */
	 public function get_dbcontext(){
	 	 return $this->dbcontext;
	 }

     /**
      * Set the object data
      * */
	 protected function set_object_data($data){
	 	$this->object_data = $data;
	 }

     /**
      * Set edit observer classes
      * */
	 protected function set_edit_observer_classes(array $observer_classes){
	 	$this->edit_attributes->observer_classes = $observer_classes;
	 }

	 /**
      * Set delete observer classes
      * */
	 protected function set_delete_observer_classes(array $observer_classes){
	 	$this->delete_attributes->observer_classes = $observer_classes;
	 }

	 /**
	  * Set edit/save data sources
	  * */
	 protected function set_data_sources(array $data_sources){
	 	$this->data_sources = $data_sources;
	 }

	 /**
	  * Set back url
	  * */
	 protected function set_back_url(string $url){
	 	$this->view_attributes->back_url = $url;
	 }

	 public function get_data_mapping(){
	 	return $this->data_mapping;
	 }

	 private function scrap_data(){
	 	 $object_data = $this->get_object_data();
	 	 $mark_delete = $this->request->data->get("mark_delete", []);
	 	 $this->delete_attributes->mark_delete = $mark_delete ?  true : false;
	 	 foreach($this->data_sources as $source_index => $source){
	 	 	 //print_r($source);
	 	 	 $data_source_fields = $source->get_fields();
	 	 	 $table_name         = $source->get_table();
	 	 	 $multiple           = $source->get_multiple();
	 	 	 $primary            = $source->get_primary();
	 	 	 $primary_key_value  = self::get_property_value($primary, $source->get_sdata());
	 	 	 $delete_key         = $table_name.":".$primary_key_value;
	 	 	 $action             = $primary_key_value ? (in_array($delete_key, $mark_delete) ? "delete" : "edit") : "add";
	 	 	 $data_source_name   = $source->get_datasource_name();
             $row_mapping = ['data' => [], 'defined' => [], 'multiple' => $multiple, 'action' => $action, 
	 	 	               "primary_key_name" => $primary, "primary_key_value" => $primary_key_value];
	 	 	 foreach($data_source_fields as $field){
	 	 		$name       = $field->get_name();
	 	 		$source     = $field->get_source();
	 	 		$row_value  = $field->get_value();
	 	 		$is_file    = $field->get_is_file() ? 'file' : 'scalar';

	 	 		$field_key_name = $name.":".$is_file;
	 	 		/**
	 	 		 * Only collect the fields whose source is form/defined and whose values exists.
	 	 		 * */
	 	 		 if($source == "form" || $source == "defined"){
	 	 			 $value = match($source){
				         'form'    => $this->request->data->get($field->get_control(), ''),
				         'defined' => $row_value,
				     };
				     $value = $is_file == "file" && $value && $value['error'] != UPLOAD_ERR_OK ? '' : $value;
	 	 			 if($value && $source == "form"){
	 	 			 	 $row_mapping['data'][$field_key_name] = $value;
	 	 			 }
	 	 			 if($value && $source == "defined"){
	 	 			 	 $row_mapping['defined'][$field_key_name] = $value;
	 	 			 }
	 	 		}
	 	 	 }
             if(!array_key_exists($data_source_name, $this->data_mapping)){
 	 		     $this->data_mapping[$data_source_name] = [];
 	 	     }
         	 $this->data_mapping[$data_source_name][] = (Object)$row_mapping;
	 	 }
	 }

	 public function post() : HttpMessage{
	 	 $template_context = $this->get()->get_response();
	 	 $this->scrap_data();
	 	 $message_context  = $this->edit_attributes->process($this);
	 	 $message_context  = $this->delete_attributes->process($this);
		 return new HttpMessage(StatusCode::OK, array_merge($template_context, $message_context));
	 }
	 public function get() : HttpMessage{
	 	 $this->view_setup();
	 	 $template_context = array_merge($this->view_attributes->process($this), $this->inject_extra_context());
	 	 return new HttpMessage(StatusCode::OK, $template_context);
	 }
}
?>