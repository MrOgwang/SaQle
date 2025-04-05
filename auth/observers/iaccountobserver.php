<?php
namespace SaQle\Auth\Observers;

use SaQle\Core\Observable\{Observer, Observable};
use SaQle\Auth\Services\AccountsService;

abstract class IAccountObserver implements Observer{
	 protected $kwargs;
     public function __construct(protected AccountsService $acc_service, ...$kwargs){
         $this->acc_service->attach($this);
		 $this->kwargs = $kwargs;
     }
     public function update(Observable $observable){
         if($observable === $this->acc_service){
             $this->do_update($observable);
         }
     }
     public abstract function do_update(AccountsService $acc_service);
}
?>