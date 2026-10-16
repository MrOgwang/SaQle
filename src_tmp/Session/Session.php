<?php

namespace SaQle\Session;

use SaQle\Http\Request\Request;
use SaQle\Core\Data\Data;
use RuntimeException;

final class Session extends Data {  

     public function start(Request $request){

         if($request->is_web_request()){

             if(session_status() === PHP_SESSION_NONE){
                 session_start();
             }  

             $this->replace((array)$_SESSION);

             $this->promote_flash($request);
         }
         
     }

     public function close(Request $request){
         
         if($request->is_web_request()){

             $flash = $this->get('__flash_current', []);

             foreach($flash as $key){
                 $this->remove($key);
             }
             
             $this->remove('__flash_current');

             //persist session
             $_SESSION = $this->data;

             session_write_close();

         }

     }

     private function promote_flash(Request $request){
         
         if($this->exists('__flash_next')){
             $this->set('__flash_current', $this->get('__flash_next'));
             $this->remove('__flash_next');
         } 

     }

     public function flash(string $key, mixed $value){

         $this->set($key, $value);

         $flash = $this->get('__flash_next', []);

         $flash[] = $key;

         $this->set("__flash_next", $flash);
     }

     public function get_flash(string $key, mixed $default = null){
         return $this->get('__flash_current', [])[$key] ?? $default;
     }

     public function has(string $key): bool {
         return $this->exists($key);
     }
}
