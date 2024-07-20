<?php
namespace SaQle\Controllers\Forms\Generics;

use SaQle\Controllers\Forms\FormController;

abstract class GenericForm extends FormController{
	 abstract public function get_setup();
	 public function form_setup(){
	 	 $setup = $this->get_setup();
	 	 if(!array_key_exists("model", $setup)){
	 	 	throw new Exception("Generic form has not specified a model class!");
	 	 }

	 	 $modelclassname = $setup['model'];
	 	 $nameparts = explode("\\", $modelclassname);
	 	 $realmodelname = end($nameparts);

	 	 /**
	 	 * If you want to do something further with save results, add observer classes here
	 	 * */
	 	 if(!array_key_exists("observers", $setup)){
	 	 	$this->set_observer_classes($setup['observers']);
	 	 }

	 	 /**
	 	 * Set the success message when a form submits successfully
	 	 * */
	 	 if(!array_key_exists("smessage", $setup)){
	 	 	$this->set_success_message($setup['smessage']);
	 	 }else{
	 	 	$this->set_success_message($realmodelname." record created successfully!");
	 	 }

	 	 /*
	 	 * Get field controls and add to form
	 	 */
	 	 $modelclassinstance = new $modelclassname();
	 	 $controls = $modelclassinstance->get_field_controls();
	 	 $title = array_key_exists("gtitle", $setup) ? $setup['gtitle'] ? "{$modelclassname} Information";
	 	 $description = array_key_exists("gdesc", $setup) ? $setup['gdesc'] ? "Provide all the required ".strtolower($modelclassname)." information";
	 	 $this->add_form_group(title: $title, description: $description, controls: $controls);

	 	 /**
	 	 * Define product information datasources
	 	 * */
	 	 $modelsource = new FormDataSource(context: $this->context[0], dao: $modelclassname);
	 	 $this->set_data_sources([$modelsource]);
	 }
}
?>