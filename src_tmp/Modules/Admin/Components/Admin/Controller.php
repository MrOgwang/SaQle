<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Http\Response\Message;

class Controller { 

     public function get() : Message {
         return Message::ok(); 
     }
} 
