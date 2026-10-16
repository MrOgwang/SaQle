<?php
namespace SaQle\Lib\Components\Error;

use SaQle\Core\Components\Abstracts\AbstractDefinition;
use SaQle\Http\HttpStatus;

final class Definition extends AbstractDefinition {
	 protected function initialize(array $props = []) : void { 

         parent::initialize($props);

         $this->context()->props()->set(
         	 'code', 
         	 request()->attributes->get('error.code', HttpStatus::INTERNAL_SERVER_ERROR)
         );

         $this->context()->props()->set(
         	 'message',
         	 request()->attributes->get('error.message', "Internal Server Error")
         );

         $this->context()->props()->set(
         	 'data', 
         	 request()->attributes->get('error.context', null)
         );
     }
} 