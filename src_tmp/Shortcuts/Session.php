<?php

use SaQle\Core\Support\Session;
use SaQle\Auth\Identity\Tenant\Interfaces\TenantInterface;
use SaQle\Auth\Context\ActorContext;
use SaQle\Auth\Identity\User\Interfaces\UserInterface;
use SaQle\Http\Request\Request;

if(!function_exists('session')){
     function session(){
         return Request::get()->session();
     }
}

if(!function_exists('errors')){
     function errors(?string $field = null){

         $errors = Session::get('__errors', []);
         
         if($field){
             return $errors[$field] ?? [];
         }

         return $errors;
     }
}

if(!function_exists('old')){
     function old(?string $field = null){
         
         $old = Session::get('__old', []);
         
         if($field){
             return $old[$field] ?? null;
         }

         return $old;
     }
}

if(!function_exists('notification')){
     function notification(){
        
         $m = Session::get('__notification');

         if($m){
             return (Object)$m;
         }

         return null;
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