<?php

namespace SaQle\Modules\Admin\Components\Admin;

use SaQle\Http\Response\Message;
use SaQle\Admin\{
     Admin as AdminProvider,
     Platform
};
use SaQle\Auth\Context\ActorContext;

class Controller { 

     public function get() : Message {
 
         return Message::ok([
             'is_platform' => ActorContext::is_platform(),
             'navigation' => ActorContext::is_platform() ? Platform::navigation() : AdminProvider::navigation(),
             'tenant_name' => config('tenancy.enabled') && request()->tenant ? request()->tenant->tenant_name : ""
         ]); 
     }
} 
