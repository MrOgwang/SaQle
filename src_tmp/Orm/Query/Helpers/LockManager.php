<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

trait LockManager {

     protected bool $update_locked = false;

     protected bool $share_locked = false;

     public function lock_for_update(){
         
         $this->before_lock();

         $this->update_locked = true;

         $this->after_lock();
     }

     public function lock_for_share(){
         
         $this->before_lock();

         $this->share_locked = true;

         $this->after_lock();
     }

     protected function before_lock(){

     }

     protected function after_lock(){
        
     }

     public function is_locked_for_update(){
         return $this->update_locked;
     }

     public function is_locked_for_share(){
         return $this->share_locked;
     }
}
