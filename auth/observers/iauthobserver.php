<?php
namespace SaQle\Auth\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Auth\Services\AuthService;

abstract class IAuthObserver implements Observer{
     public function __construct(protected AuthService $auth_service, protected string $redirect_to = ''){
         $this->auth_service->attach($this);
     }
     public function update(Observable $observable){
         if($observable === $this->auth_service){
             $this->do_update($observable);
         }
     }
     public abstract function do_update(AuthService $auth_service);
}
?>