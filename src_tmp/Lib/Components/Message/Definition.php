<?php
namespace SaQle\Lib\Components\Message;

use SaQle\Core\Components\Abstracts\AbstractDefinition;

final class Definition extends AbstractDefinition {

	 protected function initialize(array $props = []) : void { 

         parent::initialize($props);

         $notification = session()->get('__notification', null);

         $message = $notification ? $notification['message'] : null;
         $type = $notification ? $notification['type'] : null;

         $this->context()->props()->set('type', $type);
         $this->context()->props()->set('message', $message);
     }
} 