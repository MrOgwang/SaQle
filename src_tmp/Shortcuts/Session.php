<?php

use SaQle\Http\Kernel\Session;
use SaQle\Auth\Identity\Tenant\Interfaces\TenantInterface;

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