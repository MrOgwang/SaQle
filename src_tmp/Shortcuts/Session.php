<?php

use SaQle\Http\Kernel\Session;
use SaQle\Auth\Identity\Tenant\Interfaces\TenantInterface;
use SaQle\Auth\Context\ActorContext;
use SaQle\Auth\Identity\User\Interfaces\UserInterface;

if(!function_exists('flash_to_session')){
     function flash_to_session(string $key, mixed $value) {
         Session::flash($key, $value);
     }
}

if(!function_exists('flash_from_session')){
     function flash_from_session(string $key, mixed $default) : mixed {
         return Session::get_flash($key, $default);
     }
}

if(!function_exists('tenant')){
     function tenant() : ?TenantInterface {
         return Session::get('__tenant', null);
     }
}

if(!function_exists('actor')){
     function actor() : ?UserInterface {
         return ActorContext::actor();
     }
}