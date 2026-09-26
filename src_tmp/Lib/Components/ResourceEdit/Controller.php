<?php
namespace SaQle\Lib\Components\ResourceEdit;

use SaQle\Http\Response\Message;

class Controller {

	 public function get(int | string $id) : Message {

	 	 $model = $this->definition->get_props()['model'];

	 	 $object = $model::get()->where($model::get_pk_name()."__eq", $id)->first_or_fail();

		 return Message::ok([
		 	 'object' => $object
		 ]);
	 }

	 public function patch(int | string $id) : Message {

	 	 $form  = $this->definition->get_props()['form'];
	 	 $model = $this->definition->get_props()['model'];

	 	 $incoming = request()->data->get_all();

	 	 $data = array_intersect_key(
             $incoming,
             array_flip(array_keys($form->get_fields()))
         );

	 	 $saved = $model::update($data)->where($model::get_pk_name()."__eq", $id)->now();

		 return Message::redirect(route(resource_route_name("list", $model)))
		 ->with_message('success', 'Updated successfully!');
	 }
}