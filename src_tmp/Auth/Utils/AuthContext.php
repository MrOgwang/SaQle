<?php
namespace SaQle\Auth\utils;

class AuthContext {
     public static function set(): void {

         $uri = request()->uri();

         $auth_context = str_starts_with($uri, '/saqle/') ? 'saqle' : 'tenant';

         request()->attributes->set('__auth_context', $auth_context);
     }

     public static function get(){
         return request()->attributes->get(
             '__auth_context',
             request()->session->get('__auth_context', 'tenant')
         );
     }
}