<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Http\Message\Message;

class Controller { 

     public function get() : Message {
         return Message::ok(); 
     }
} 
