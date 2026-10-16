<?php
namespace SaQle\Lib\Components\ResourceEdit;

use SaQle\Http\Message\Message;
use SaQle\Core\Components\Abstracts\AbstractController;

class Controller extends AbstractController { 

	 public function get(int | string $id) : Message {

	 	 $model = $this->context()->props()->get_or_fail('model'); 

	 	 $object = $model::get()->where($model::get_pk_name()."__eq", $id)->first_or_fail();

	 	 return Message::ok()->with('object', $object);
	 }

	 public function patch(int | string $id) : Message {

	 	 $form  = $this->context()->props()->get_or_fail('form');
	 	 $model = $this->context()->props()->get_or_fail('model');

	 	 $incoming = request()->data->get_all();

	 	 $data = array_intersect_key(
             $incoming,
             array_flip(array_keys($form->get_fields()))
         );

	 	 $saved = $model::update($data)->where($model::get_pk_name()."__eq", $id)->now();

		 return Message::redirect(route(resource_route_name("list", $model)))
		 ->with('message', [
		 	 'type' => 'success',
		 	 'message' => 'Updated successfully!'
		 ]);
	 }
}