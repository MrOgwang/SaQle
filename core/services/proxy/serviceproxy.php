<?php
namespace SaQle\Core\Services\Proxy;

use SaQle\Core\Services\IService;
use SaQle\Core\Services\Observer\ServiceObserver;
use SaQle\Core\Observable\{Observable, ConcreteObservable};
use SaQle\Core\Services\Attr\ResultName;

class ServiceProxy implements Observable, IService{
     use ConcreteObservable {
         ConcreteObservable::__construct as private __coConstruct;
     }

     public function __construct(private IService $service){
         $this->__coConstruct();
     }

     public function __call(string $method, array $args){
         $ref_method = new \ReflectionMethod($this->service, $method);
         $parameters = $ref_method->getParameters();

         $arg_meta = [];
         foreach($parameters as $index => $param){
             $type = $param->getType();
             $arg_meta[] = [
                 'name'   => $param->getName(),
                 'type'   => $type ? $type->getName() : null,
                 'value'  => $args[$index] ?? null,
             ];
         }


         //send pre signal to observers
         $preobservers = ServiceObserver::get_service_observers('before', $this->service::class, $method);

         if($preobservers){
             $this->quick_notify(observers: $preobservers, args: $args, args_meta: $arg_meta);
         }

         $result = call_user_func_array([$this->service, $method], $args);

         //send post signal to observers
         $postobservers = ServiceObserver::get_service_observers('after', $this->service::class, $method);

         if($postobservers){
             //check for ResultName attribute
             $result_name = 'result';
             foreach ($ref_method->getAttributes(ResultName::class) as $attr){
                 $result_name = $attr->newInstance()->name;
             }

             $arg_meta[] = [
                'name'  => $result_name,
                'type'  => is_object($result) ? get_class($result) : gettype($result),
                'value' => $result,
             ];
            
            $this->quick_notify(observers: $postobservers, args: array_merge($args, [$result]), args_meta: $arg_meta);
         }

         return $result;
     }

     public function __get(string $name){
         return $this->service->$name;
     }

     public function __set(string $name, $value){
         $this->service->$name = $value;
     }
}

