<?php
namespace SaQle\Lib\Components\ResourceCreate;

use SaQle\Http\Response\Message;

class Controller {

	 public function get() : Message { 
	 	 return Message::ok();
	 }

	 public function post() : Message {

	 	 $form  = $this->definition->get_props()['form'];
	 	 $model = $this->definition->get_props()['model'];

	 	 $incoming = request()->data->get_all();
	 	 
	 	 $data = array_intersect_key(
             $incoming,
             array_flip(array_keys($form->get_fields()))
         );
	 	 
	 	 $saved = $model::create($data)->now();

		 return Message::redirect()->with_message('success', 'Created successfully!');
	 }
}