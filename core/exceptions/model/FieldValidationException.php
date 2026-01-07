<?php
namespace SaQle\Core\Exceptions\Model;

use SaQle\Core\Exceptions\Base\DomainException;
use SaQle\Core\FeedBack\FeedBack;

/**
 * This exception is thrown when a field validation fails
 * on a model
 * */

class FieldValidationException extends DomainException {

	 public function __construct(array $context){
         parent::__construct(
             message   : $this->construct_message($context),
             code      : FeedBack::BAD_REQUEST,
             context   : $context
         );
     }

     private function construct_message(array $context){
     	 $message = "Model: ".$context['model'].", Operation: ".$context['operation'].", Error: One or more fields failed field validation as follows: \n";
		 foreach($context['dirty'] as $field => $error){
			 $message .= "\n".$field." : ".$error."\n";
		 }
         return $message;
     }

}
