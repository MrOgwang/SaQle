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

abstract class AddController extends IController implements Observable{
	 public $model_form;
	 private $model_name_property;

	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }

	 public function __construct(Request $request, array $context = [], ...$kwargs){
	 	 if(!$this->model_form){
	 	 	throw new \Exception("A model form must be provided for a generic add controller!");
	 	 }

	 	 /**
	 	  * Set the name property of model.
	 	  * */
	 	 $this->set_name_property();

		 $this->__coConstruct();
		 parent::__construct($request, $context, $kwargs);
	 }

	 /*public function get_desired_template(){
	 	 return "backoffice.clientdashboard" : "admin.providerdashboard";
	 }*/

	 private function set_name_property(){
	 	 $model_form          = $this->model_form;
	 	 $model_form_instance = new $model_form();
	 	 $model_name_parts    = explode("\\", $model_form_instance->model);
	 	 $model_name          = end($model_name_parts);
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $this->model_name_property = $model_schema->get_name_property();
	 }

	 private function extract_data($form_class, $multiple = false){
	 	 $model_form_instance = new $form_class();
	 	 $model_name_parts    = explode("\\", $model_form_instance->model);
	 	 $model_name          = end($model_name_parts);
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $fields              = $model_schema->get_all_fields();
	 	 $extra_tabs          = [];

	 	 $defined_field_names = $model_schema->get_defined_field_names();

         $data = [];
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
		 	 		 	 $data[$fn] = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
			 	 	     continue;
		 	 		 }

		 	 		 /**
		 	 		  * For foreign key fields, pull the records from
		 	 		  * the model pointed by this key and provide a select box options of those records.
		 	 		  * */
		 	 		 if($f instanceof Relation && !$f->is_navigation()){
		 	 		 	 $pkvalue = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
		 	 		 	 if($pkvalue){
		 	 		 	 	 $fkrecord = $this->fetch_fk_record($pkvalue, $f);
		 	 		 	 	 if($fkrecord){
		 	 		 	 	 	$data[$fn] = $fkrecord;
		 	 		 	 	 }
		 	 		 	 }
		 	 		 	 continue;
		 	 		 }

		 	 		 /**
		 	 		  * For navigation key fields
		 	 		  * */
		 	 		 if($f instanceof Relation && $f->is_navigation()){
		 	 		 	 $relation = $f->get_relation();

		 	 		 	 if(array_key_exists($fn, $model_form_instance->edit_with_new)){
		 	 		 	 	 $extra_tabs[$fn] = [
		 	 		 	 	 	$model_form_instance->edit_with_new[$fn], 
		 	 		 	 	 	$relation instanceof One2Many || $relation instanceof Many2Many ? true : false
		 	 		 	 	 ];
		 	 		 	 	 continue;
		 	 		 	 }

		 	 		 	 if(array_key_exists($fn, $model_form_instance->edit_with_existing)){
		 	 		 	 	 
		 	 		 	 	 if($relation instanceof One2One){
		 	 		 	 	 	 $pkvalue = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, '');
		 	 		 	 	 	 if($pkvalue){
				 	 		 	 	 $fkrecord = $this->fetch_fk_record($pkvalue, $f);
				 	 		 	 	 if($fkrecord){
				 	 		 	 	 	$data[$fn] = $fkrecord;
				 	 		 	 	 }
				 	 		 	 }
				 	 		 	 continue;
		 	 		 	 	 }

		 	 		 	 	 if($relation instanceof One2Many || $relation instanceof Many2Many){
		 	 		 	 	 	 $pkvalues = $required ? $this->request->data->get($fn) : $this->request->data->get($fn, []);
		 	 		 	 	 	 if($pkvalues){
			 	 		 	 	 	 $fkrecords = $this->fetch_fk_record($pkvalues, $f, true);
			 	 		 	 	 	 if(count($fkrecords) > 0){
			 	 		 	 	 	 	 $data[$fn] = $fkrecords;
			 	 		 	 	 	 }
			 	 		 	 	 }
			 	 		 	 	 continue;
		 	 		 	 	 }
		 	 		 	 }
		 	 		 }
		 	 	 }elseif(array_key_exists($fn, $model_form_instance->pre_defined)){
		 	 	 	 if(is_callable($model_form_instance->pre_defined[$fn])){
		 	 	 	 	$callback = $model_form_instance->pre_defined[$fn];
		 	 	 	 	$data[$fn] = $callback((object)$data);
		 	 	 	 	continue;
		 	 	 	 }

		 	 	 	 $data[$fn] = $model_form_instance->pre_defined[$fn];
		 	 	 }
	 	 	}
	 	 }

	 	 if($extra_tabs){
	 	 	 foreach($extra_tabs as $tk => $tf){
	 	 	 	 $data[$tk] = $this->extract_data($tf[0], $tf[1]);
	 	     }
	 	 }

	 	 $daoname = $model_form_instance->model;
	 	 if($multiple){
	 	 	 $collection_class = $daoname::get_collection_class();
	 	 	 return new $collection_class([new $daoname(...$data)]);
	 	 }

	 	 return new $daoname(...$data);
	 }
	 
	 public function post() : HttpMessage{
	 	 $context = $this->get()->get_response();
	 	 try{
	 	 	 $data = $this->extract_data($this->model_form);
	 	 	 $saved = $data->save();

	 	 	 $_SESSION['success'] = true;
	 	 	 $_SESSION['message'] = "Object was added successfully!";

	 	 	 $this->reload();
	 	 }catch(\Exception $ex){
	 	 	 $_SESSION['success'] = false;
	 	 	 $_SESSION['message'] = $ex->getMessage();
	 	 }

		 return new HttpMessage(StatusCode::OK, $context);
	 }

     private function fetch_fk_record(array | string $pkvalue, $f, $multiple = false){
     	 $relation   = $f->get_relation();
	 	 $fdaoschema = $relation->get_fdao();
	 	 $state      = $fdaoschema::state();
	 	 $fdaomodel  = $fdaoschema::get_associated_model_class();
	 	 $pkname     = $state->get_pk_name();

	 	 if(!$multiple){
	 	 	 return $fdaomodel::db()->where($pkname, is_array($pkvalue) ? $pkvalue[0] : $pkvalue)->tomodel(true)->first_or_default();
	 	 }
	 	 return $fdaomodel::db()->where($pkname."__in", !is_array($pkvalue) ? [$pkvalue] : $pkvalue)->tomodel(true)->all();
     }

     private function get_fk_records($f){
     	 $relation   = $f->get_relation();
	 	 $fdaoschema = $relation->get_fdao();
	 	 $state      = $fdaoschema::state();
	 	 $fdaomodel  = $fdaoschema::get_associated_model_class();
	 	 $pkname     = $state->get_pk_name();
	 	 $nameprop   = $state->get_name_property();

	 	 return [$pkname, $nameprop, $fdaomodel::db()->all()];
     }

	 private function create_fk_select($f, $multiple = false){
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
	 	 $control    = new FormControl(...$ctrlattrs);
	 	 return $control->get_control();
	 }

     private function create_form($form_class, $counter = 0){
	 	 $model_form_instance = new $form_class();
	 	 $model_name_parts    = explode("\\", $model_form_instance->model);
	 	 $model_name          = end($model_name_parts);
	 	 $model_schema        = $model_form_instance->model::get_schema();
	 	 $fields              = $model_schema->get_all_fields();
	 	 $extra_tabs          = [];

	 	 $defined_field_names = $model_schema->get_defined_field_names();

	 	 $controls = "";
	 	 foreach($fields as $fn => $f){
	 	 	/**
	 	 	 * For a control to be generated, the field must have been explicitly defined
	 	 	 * in the schema, and it must be listed in the from_form property of model form.
	 	 	 * */
	 	 	if(in_array($fn, $defined_field_names) && in_array($fn, $model_form_instance->from_form)){
	 	 		 /**
	 	 		  * For non relation fields, create a control straight away
	 	 		  * */
	 	 		 if(!$f instanceof Relation){
	 	 		 	 $control_attributes = $f->get_control_attributes();
		 	 	     $control = new FormControl(...$control_attributes);
		 	 	     $controls .= $control->get_control();
		 	 	     continue;
	 	 		 }

	 	 		 /**
	 	 		  * For foreign key fields, pull the records from
	 	 		  * the model pointed by this key and provide a select box options of those records.
	 	 		  * */
	 	 		 if($f instanceof Relation && !$f->is_navigation()){
	 	 		 	 $controls .= $this->create_fk_select($f);
	 	 		 	 continue;
	 	 		 }

	 	 		 /**
	 	 		  * For navigation key fields
	 	 		  * */
	 	 		 if($f instanceof Relation && $f->is_navigation()){
	 	 		 	 if(array_key_exists($fn, $model_form_instance->create_with_new)){
	 	 		 	 	 $extra_tabs[$fn] = $model_form_instance->create_with_new[$fn];
	 	 		 	 	 continue;
	 	 		 	 }

	 	 		 	 if(array_key_exists($fn, $model_form_instance->create_with_existing)){
	 	 		 	 	 $relation = $f->get_relation();
	 	 		 	 	 $multiple = $relation instanceof One2Many || $relation instanceof Many2Many ? true : false;
	 	 		 	 	 $controls .= $this->create_fk_select($f, $multiple);
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