<?php
namespace SaQle\Core\Services\Proxy;

use SaQle\Core\Services\IService;
use SaQle\Core\Services\Observer\ServiceObserver;
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\FeedBack\FeedBack;

class ServiceProxy implements Observable, IService{
     use ConcreteObservable {
         ConcreteObservable::__construct as private __coConstruct;
     }

     public function __construct(private IService $service){
         $this->__coConstruct();
     }

     /*public function record_signin(string $user_id){
         echo "Recording sign in for user: $user_id\n";
         //send post signal to observers
         $postobservers = array_merge(
             ServiceObserver::get_service_observers('before', $this->service::class, 'record_signin'), 
             ServiceObserver::get_shared_observers('before')
         );
         
         print_r($postobservers);
     }*/

     public function __call(string $method, array $args){

         //send pre signal to observers
         $preobservers = array_merge(
             ServiceObserver::get_service_observers('before', $this->service::class, $method), 
             ServiceObserver::get_shared_observers('before')
         );

         if($preobservers){
             $this->quick_notify(
                 observers: $preobservers,
                 code: FeedBack::OK, 
                 data: [
                     'service' => $this->service::class, 
                     'method'  => $method, 
                     'args'    => $args
                 ]
             );
         }

         $result = call_user_func_array([$this->service, $method], $args);

         //send post signal to observers
         $postobservers = array_merge(
             ServiceObserver::get_service_observers('after', $this->service::class, $method), 
             ServiceObserver::get_shared_observers('after')
         );

         if($postobservers){
             $this->quick_notify(
                 observers: $postobservers,
                 code: FeedBack::OK, 
                 data: [
                     'service' => $this->service::class, 
                     'method'  => $method, 
                     'args'    => $args,
                     'result'  => $result
                 ]
             );
         }
         

         /*$ref = new ReflectionMethod($this->service, $method);
         foreach ($ref->getAttributes() as $attribute) {
             $attrInstance = $attribute->newInstance();

             if ($attrInstance instanceof Loggable) {
                 echo "[LOG]: {$attrInstance->message}\n";
             }

            // Add other decorators here
         }

         return $ref->invokeArgs($this->service, $args);*/

         return $result;
     }

     public function __get(string $name){
         return $this->service->$name;
     }

     public function __set(string $name, $value){
         $this->service->$name = $value;
     }
}
?>
