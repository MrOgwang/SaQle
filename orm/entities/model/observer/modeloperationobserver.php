<?php
namespace SaQle\Orm\Entities\Model\Observer;

use SaQle\Core\Observable\{Observer, Observable};
use SaQle\Orm\Entities\Model\Interfaces\IOperationManager;

abstract class ModelOperationObserver implements Observer{
     public function update(Observable $observable){
         $this->handle($observable);
     }

     public abstract function handle(IOperationManager $manager);
}
