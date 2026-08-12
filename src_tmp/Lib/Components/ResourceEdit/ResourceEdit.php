<?php
namespace SaQle\Lib\Components\ResourceEdit;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\{
	 FormMode, 
	 FormContext
};
use SaQle\Routing\Resources\ResourceRouteUtils;
use RuntimeException;

class ResourceEdit {

	 use ResourceRouteUtils;

	 public function get(int | string $id, array $__props) : Message {

	 	 $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";

		 $form = $this->create_auto_form(FormMode::UPDATE, $__props);

	 	 if(!$form){
	 	 	 throw new RuntimeException("Unknown resource form requested!");
	 	 }

	 	 $object = $model_class::get()->where($model_class::get_pk_name()."__eq", $id)->first_or_fail();
	 	 $form->bind(FormContext::make($object), request());

		 return Message::ok([
		 	 'form' => $form,
		 	 'object' => $object,
		 	 'resource' => $this->resource($model_class)
		 ]);
	 }

	 public function patch(int | string $id) : Message {

	 	 $form = $this->create_auto_form(FormMode::UPDATE);

	 	 $incoming = request()->data->get_all();
	 	 $data = array_intersect_key(
             $incoming,
             array_flip(array_keys($form->get_fields()))
         );

	 	 $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";
	 	 
	 	 $saved = $model_class::update($data)->where($model_class::get_pk_name()."__eq", $id)->now();

		 return Message::redirect(route(resource_route_name("list", $model_class)))
		 ->with_message('success', 'Updated successfully!');
	 }
}