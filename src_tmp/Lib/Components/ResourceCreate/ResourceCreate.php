<?php
namespace SaQle\Lib\Components\ResourceCreate;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\{
	 FormMode, 
	 FormContext
};
use SaQle\Routing\Resources\ResourceRouteUtils;
use RuntimeException;

class ResourceCreate {

	 use ResourceRouteUtils;

	 public function get(array $__props) : Message {
	 	 $incoming = request()->data->get_all();
	 	
	 	 $form = $this->create_auto_form(FormMode::CREATE, $__props);
	 	 $form->bind(FormContext::make(), request());

	 	 if(!$form){
	 	 	 throw new RuntimeException("Unknown resource form requested!");
	 	 }

		 return Message::ok([
		 	 'form' => $form,
		 	 'resource' => $this->resource(request()->route->model_class)
		 ]);
	 }

	 public function post() : Message {

	 	 $form = $this->create_auto_form(FormMode::CREATE);

	 	 $incoming = request()->data->get_all();
	 	 
	 	 $data = array_intersect_key(
             $incoming,
             array_flip(array_keys($form->get_fields()))
         );

	 	 $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";
	 	 
	 	 $saved = $model_class::create($data)->now();

		 return Message::redirect()->with_message('success', 'Created successfully!');
	 }
}