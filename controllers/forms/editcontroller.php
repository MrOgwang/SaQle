<?php
namespace SaQle\Controllers\Forms;

use SaQle\Controllers\IController;
use SaQle\Http\Request\Request;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Http\Response\{HttpMessage, StatusCode};
use SaQle\Dao\Field\Controls\FormControl;
use SaQle\Dao\Field\Types\Base\Relation;
use SaQle\Dao\Field\FormControlTypes;
use SaQle\Dao\Field\Relations\{One2One, One2Many, Many2Many};
use SaQle\Dao\Field\Types\{FileField};

abstract class EditController extends IController implements Observable{
	 public  $model_form;
	 private $object;

	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }

	 public function __construct(Request $request, array $context = [], ...$kwargs){
	 	 if(!$this->model_form){
	 	 	throw new \Exception("A model form must be provided for a generic edit controller!");
	 	 }

		 $this->__coConstruct();
		 parent::__construct($request, $context, $kwargs);

		 /**
	 	  * Make sure an id is available.
	 	  * */
	 	 $params = $this->request->route->get_params();
	 	 if(!isset($params['id'])){
	 	 	 throw new \Exception("Object to edit id not provided!");
	 	 }

	 	 /**
	 	  * Set the object to edit.
	 	  * */
	 	 $this->set_object();
	 }

	 private function set_object(){
	 	 $model_form          = $this->model_form;
	 	 $model_form_instance = new $model_form();
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $pkname              = $model_schema->meta->pk_name;
	 	 $with_fields         = [];
	 	 $manager             = $model_form_instance->model::db();

	 	 if(count($model_form_instance->edit_with_existing) > 0){
	 	 	 $manager->with(array_keys($model_form_instance->edit_with_existing));
	 	 }
	 	 $this->object        = $manager->where($pkname, $this->request->route->get_params()['id'])->tomodel(true)->first_or_default();
	 	 if(!$this->object){
	 	 	 throw new \Exception("Object to edit doesn't exists!");
	 	 }
	 }

	 private function extract_data($form_class, $multiple = false){
	 	 $model_form_instance = new $form_class();
	 	 $model_name_parts    = explode("\\", $model_form_instance->model);
	 	 $model_name          = end($model_name_parts);
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $fields              = $model_schema->meta->fields;
	 	 $extra_tabs          = [];

	 	 $defined_field_names = $model_schema->meta->defined_field_names;
	 	 foreach($fields as $fn => $f){
	 	 	/**
	 	 	 * For a control to be generated, the field must have been explicitly defined
	 	 	 * in the schema, and it must be listed in the from_form property of model form.
	 	 	 * */
	 	 	if(in_array($fn, $defined_field_names)){
	 	 		 if(in_array($fn, $model_form_instance->from_form)){
		 	 		 $required = $f->is_required();

		 	 		 /**
		 	 		  * For non relation fields, create a data entry straight away
		 	 		  * */
		 	 		 if(!$f instanceof Relation){
		 	 		 	 $this->object->$fn = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
			 	 	     continue;
		 	 		 }

		 	 		 /**
		 	 		  * For foreign key fields, pull the records from
		 	 		  * the model pointed by this key and provide a select box options of those records.
		 	 		  * */
		 	 		 if($f instanceof Relation && !$f->is_navigation()){
		 	 		 	 $pkvalue = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
		 	 		 	 $fkrecord = $this->fetch_fk_record($pkvalue, $f);
		 	 		 	 $this->object->$fn = $fkrecord;
		 	 		 	 continue;
		 	 		 }

		 	 		 /**
		 	 		  * For navigation key fields
		 	 		  * */
		 	 		 if($f instanceof Relation && $f->is_navigation()){
		 	 		 	 $relation = $f->get_relation();

		 	 		 	 if(array_key_exists($fn, $model_form_instance->create_with_new)){
		 	 		 	 	 $extra_tabs[$fn] = [
		 	 		 	 	 	$model_form_instance->create_with_new[$fn], 
		 	 		 	 	 	$relation instanceof One2Many || $relation instanceof Many2Many ? true : false
		 	 		 	 	 ];
		 	 		 	 	 continue;
		 	 		 	 }

		 	 		 	 if(array_key_exists($fn, $model_form_instance->create_with_existing)){
		 	 		 	 	 
		 	 		 	 	 if($relation instanceof One2One){
		 	 		 	 	 	 $pkvalue = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
		 	 		 	 	 	 $fkrecord = $this->fetch_fk_record($pkvalue, $f);
		 	 		 	 	 	 $this->object->$fn = $fkrecord;
				 	 		 	 continue;
		 	 		 	 	 }

		 	 		 	 	 if($relation instanceof One2Many || $relation instanceof Many2Many){
		 	 		 	 	 	 $pkvalues = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, []);
		 	 		 	 	 	 $fkrecords = $this->fetch_fk_record($pkvalues, $f, true);
		 	 		 	 	 	 $this->object->$fn = $fkrecords;
			 	 		 	 	 continue;
		 	 		 	 	 }
		 	 		 	 }
		 	 		 }
		 	 	 }elseif(array_key_exists($fn, $model_form_instance->pre_defined)){
		 	 	 	 if(is_callable($model_form_instance->pre_defined[$fn])){
		 	 	 	 	$callback = $model_form_instance->pre_defined[$fn];
		 	 	 	 	$this->object->$fn = $callback($this->object);
		 	 	 	 	continue;
		 	 	 	 }

		 	 	 	 $this->object->$fn = $model_form_instance->pre_defined[$fn];
		 	 	 }
	 	 	}
	 	 }

	 	 if($extra_tabs){
	 	 	 foreach($extra_tabs as $tk => $tf){
	 	 	 	 $this->object->$tk = $this->extract_data($tf[0], $tf[1]);
	 	     }
	 	 }
	 }
	 
	 public function post() : HttpMessage{
	 	 $context = $this->get()->get_response();
	 	 try{
	 	 	 $this->extract_data($this->model_form);
	 	 	 $this->object->save();

	 	 	 $_SESSION['success'] = true;
	 	 	 $_SESSION['message'] = "Object was edited successfully!";

	 	 	 $this->reload();
	 	 }catch(\Exception $ex){
	 	 	 $_SESSION['success'] = false;
	 	 	 $_SESSION['message'] = $ex->getMessage();
	 	 }

		 return new HttpMessage(StatusCode::OK, $context);
	 }

     private function fetch_fk_record(array | string $pkvalue, $f, $multiple = false){
     	 $relation    = $f->get_relation();
	 	 $fmodelclass = $relation->get_fmodel();
	 	 $state       = $fmodelclass::state();
	 	 $pkname     = $state->meta->pk_name;

	 	 if(!$multiple){
	 	 	 return $fmodelclass::db()->where($pkname, is_array($pkvalue) ? $pkvalue[0] : $pkvalue)->tomodel(true)->first_or_default();
	 	 }
	 	 return $fmodelclass::db()->where($pkname."__in", !is_array($pkvalue) ? [$pkvalue] : $pkvalue)->tomodel(true)->all();
     }

     private function get_fk_records($f){
     	 $relation    = $f->get_relation();
	 	 $fmodelclass = $relation->get_fmodel();
	 	 $state       = $fmodelclass::state();
	 	 $pkname      = $state->meta->pk_name;
	 	 $nameprop    = $state->meta->name_property;

	 	 return [$pkname, $nameprop, $fmodelclass::db()->all()];
     }

	 private function create_fk_select($f, $optional, $multiple = false, $currentvalue = null){
	 	 $ctrlattrs  = $f->get_control_attributes();
	 	 [$pkname, $nameprop, $records] = $this->get_fk_records($f);
	 	 $options    = [];
	 	 foreach($records as $record){
	 	 	 $options[$record->$pkname] = $record->$nameprop;
	 	 }
	 	 $ctrlattrs  = array_merge($ctrlattrs, [
	 	 	'type'    => 'select',
	 	 	'options' => $options
	 	 ]);
	 	 if($multiple){
	 	 	 $ctrlattrs['multiple'] = true;
	 	 }

	 	 if($currentvalue){
	 	 	 $ctrlattrs['default'] = $currentvalue->get_field_value($pkname);
	 	 }

	 	 if($optional && isset($ctrlattrs['required'])){
	 	 	unset($ctrlattrs['required']);
	 	 }

	 	 $control    = new FormControl(...$ctrlattrs);
	 	 return $control->get_control();
	 }

     private function create_form($form_class, $counter = 0){
	 	 $model_form_instance = new $form_class();
	 	 $model_name_parts    = explode("\\", $model_form_instance->model);
	 	 $model_name          = end($model_name_parts);
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $fields              = $model_schema->meta->fields;
	 	 $extra_tabs          = [];

	 	 $defined_field_names = $model_schema->meta->defined_field_names;

	 	 $controls = "";
	 	 foreach($fields as $fn => $f){
	 	 	/**
	 	 	 * For a control to be generated, the field must have been explicitly defined
	 	 	 * in the schema, and it must be listed in the from_form property of model form.
	 	 	 * */
	 	 	if(in_array($fn, $defined_field_names) && in_array($fn, $model_form_instance->from_form)){
	 	 		 $fvalue = $this->object->$fn;
	 	 		 $optional = in_array($fn, $model_form_instance->optional_update) ? true : false;

	 	 		 /**
	 	 		  * For non relation fields, create a control straight away
	 	 		  * */
	 	 		 if(!$f instanceof Relation){
	 	 		 	 $control_attributes = $f->get_control_attributes();
	 	 		 	 if(!$f instanceof FileField){
	 	 		 	 	 $control_attributes['default'] = $fvalue;
	 	 		 	 }
	 	 		 	 if( $optional && isset($control_attributes['required']) ){
	 	 		 	 	 unset($control_attributes['required']);
	 	 		 	 }
		 	 	     $control = new FormControl(...$control_attributes);
		 	 	     $controls .= $control->get_control();
		 	 	     continue;
	 	 		 }

	 	 		 /**
	 	 		  * For foreign key fields, pull the records from
	 	 		  * the model pointed by this key and provide a select box options of those records.
	 	 		  * */
	 	 		 if($f instanceof Relation && !$f->is_navigation()){
	 	 		 	 $controls .= $this->create_fk_select($f, $optional, $fvalue);
	 	 		 	 continue;
	 	 		 }

	 	 		 /**
	 	 		  * For navigation key fields
	 	 		  * */
	 	 		 if($f instanceof Relation && $f->is_navigation()){
	 	 		 	 if(array_key_exists($fn, $model_form_instance->edit_with_new)){
	 	 		 	 	 $extra_tabs[$fn] = $model_form_instance->edit_with_new[$fn];
	 	 		 	 	 continue;
	 	 		 	 }

	 	 		 	 if(array_key_exists($fn, $model_form_instance->edit_with_existing)){
	 	 		 	 	 $relation = $f->get_relation();
	 	 		 	 	 $multiple = $relation instanceof One2Many || $relation instanceof Many2Many ? true : false;
	 	 		 	 	 $controls .= $this->create_fk_select($f, $optional, $multiple, $fvalue);
	 	 		 	 	 continue;
	 	 		 	 }
	 	 		 }
	 	 	}
	 	 }

	 	 $controlsmain = $counter > 0 ? "<div class='hide addformtabsdiv'>".$controls."</div>" : "<div class='addformtabsdiv'>".$controls."</div>";

	 	 if($extra_tabs){
	 	 	 $tablinks      = "<a class='focused flex v_center' href='#'>{$model_name}</a>";
	 	 	 $othertabs     = "";
	 	 	 foreach($extra_tabs as $tk => $tf){
	 	 	 	 $counter++;
	 	 	 	 $tablinks  .= "<a class='flex v_center' href='#'>".ucwords($tk)."</a>";
         	     $othertabs .= $this->create_form($tf, $counter);
	 	     }

	 	     return "
	 	     <div class='addformtabs'>
	             <div class='flex v_center addformtabsheader'>
	                {$tablinks}
	             </div>
	             <div class='addformtabsbody'>
	                {$controlsmain}
	                {$othertabs}
	             </div>
	             <div class='flex v_center addformtabsfooter'>
	                 <div class='flex v_center'>
	                    <button id='addform-prev' class='hide flex center' data-dir='prev' data-index='0' data-tabs_count='{$counter}' type='button'>
	                    <i class='wsvert' data-lucide='arrow-left'></i>&nbsp;Previous
	                    </button>
	                 </div>
	                 <div class='flex v_center row_reverse'>
	                    <button id='addform-next' class='flex center' data-dir='next' data-index='0' data-tabs_count='{$counter}' type='button'>
	                        Next&nbsp;<i class='wsvert' data-lucide='arrow-right'></i>
	                    </button>
	                 </div>
	             </div>
	         </div>
	 	     ";
	 	 }

	 	 return $controlsmain;
     }

	 public function get() : HttpMessage{
	 	 $controls = $this->create_form($this->model_form);
	 	 $message = "";
	 	 if(isset($_SESSION['success'])){
	 	 	 $m = $_SESSION['message'];
	 	 	 if($_SESSION['success'] === true){
	 	 	 	 $message = "
	 	 	 	 <div class='addformmessage-main system-info system-info-success'>
		             {$m}
		        </div>
	 	 	 	";
	 	 	 }else{
	 	 	 	 $message = "
	 	 	 	 <div class='addformmessage-main system-info system-info-danger'>
		             {$m}
		        </div>
	 	 	 	";
	 	 	 }

	 	 	 unset($_SESSION['success']);
	 	 	 unset($_SESSION['message']);
	 	 }

	 	 return new HttpMessage(StatusCode::OK, ['controls' => $controls, 'message' => $message]);
	 }
}
?>