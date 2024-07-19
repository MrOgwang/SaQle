<?php
namespace SaQle\Controllers\Observers;

use SaQle\Observable\{Observer, Observable};
use SaQle\Controllers\Forms\FormController;

abstract class IFormControllerObserver implements Observer{
	 protected $kwargs;
     public function __construct(protected FormController $controller, ...$kwargs){
         $this->controller->attach($this);
		 $this->kwargs = $kwargs;
     }
     public function update(Observable $observable){
         if($observable === $this->controller){
             $this->do_update($observable);
         }
     }
     public abstract function do_update(FormController $controller);
}
?>