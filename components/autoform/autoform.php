<?php
namespace SaQle\Components\AutoForm;

use SaQle\Http\Response\Message;
use SaQle\Core\Ui\Forms\Form;

class AutoForm {
	 
	 public function get(array $__props) : Message {
	 	
	 	 $form = Form::make($__props['name']); 

		 return Message::ok([
		 	 'form' => $form
		 ]);
	 }

}
?>