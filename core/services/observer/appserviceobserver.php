<?php
namespace SaQle\Core\Services\Observer;

use SaQle\Core\Observable\{Observer, Observable};
use SaQle\Core\Services\IService;

abstract class AppServiceObserver implements Observer {
     public function update(Observable $observable) {
         $this->handle($observable);
     }

     public abstract function handle(IService $service);
}
