<?php
namespace SaQle\Controllers\Data;

use SaQle\Controllers\IController;
use SaQle\Http\Request\Request;
use SaQle\Views\TemplateView;
use SaQle\Views\TemplateOptions;
use SaQle\Views\FormGroup;
use SaQle\Dao\Field\Controls\FormControlCollection;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Http\Response\{HttpMessage, StatusCode};

abstract class DataPanelController extends IController implements PanelSetup, Observable{
	 private ?PanelHeader $header     = null;
	 private ?PanelBody   $body       = null;
	 private ?PanelFooter $footer     = null;
	 private string       $empty_view = "";

	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 public function __construct(Request $request, array $context = [], ...$kwargs){
		 $this->__coConstruct();
		 parent::__construct($request, $context, $kwargs);
	 }
	 abstract public function panel_setup();
	 public function inject_extract_context(){
	 	return [];
	 }
	 public function set_header(PanelHeader $header){
	     $this->header = $header;
	 }
	 public function set_body(PanelBody $body){
		 $this->body = $body;
	 }
	 public function set_footer(PanelFooter $footer){
		 $this->footer = $footer;
	 }
	 public function set_empty_view($view){
	 	$this->empty_view = $view;
	 }
	 public function get() : HttpMessage{
	 	 $this->panel_setup();

	 	 $data_panel_class = "";
	 	 if($this->header && $this->footer){
	 	 	 $data_panel_class = "data-panel-complete";
	 	 }elseif($this->header && !$this->footer){
	 	 	 $data_panel_class = "data-panel-withheader";
	 	 }elseif(!$this->header && $this->footer){
	 	 	 $data_panel_class = "data-panel-withfooter";
	 	 }elseif(!$this->header && !$this->footer){
	 	 	 $data_panel_class = "data-panel-tableonly";
	 	 }
	 	 if($this->body->get_data()){
	 	 	 $context = ["data_panel" => "
		 	 <div class='data-panel {$data_panel_class}'>
			    {$this->header->construct_panel_header(add_checkboxes: $this->body->get_add_checkboxes())}
			    {$this->body->construct_panel_body($this->header->get_item_actions())}
			 </div>"];
	 	 }else{
	 	 	$context = ["data_panel" => "
		 	 <div class='data-panel {$data_panel_class}'>
			    {$this->header->construct_panel_header($this->body->get_add_checkboxes(), false)}
			    {$this->empty_view}
			 </div>"];
	 	 }
	 	 $context = array_merge($context, $this->inject_extract_context());
         return new HttpMessage(StatusCode::OK, $context);
     }
}
?>