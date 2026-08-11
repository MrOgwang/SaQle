<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Http\Response\Message;
use SaQle\Core\Support\Index;
use SaQle\Admin\Admin as AdminProvider;

class Admin { 

     #[Index]
     public function get() : Message {

         return Message::ok([
             'navigation' => AdminProvider::navigation(),
             'tenant_name' => config('tenancy.enabled') && request()->tenant ? request()->tenant->tenant_name : ""
         ]); 
     }
} 
