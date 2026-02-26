<?php

namespace SaQle\Http\Request\Execution;

/**
 * The ParameterResolver is responsible for automatically injecting parameters into
 * controller methods.
 * 
 * Using reflection, the parameter resolver decides where to extract the values
 * to inject into a controller method
 * */

use ReflectionMethod;
use SaQle\Http\Request\Request;
use SaQle\Core\Support\{RequestContract, BindFrom};
use SaQle\Http\Request\Data\Sources\Managers\HttpDataSourceManager;
use SaQle\Orm\Entities\Model\Schema\Model;
use RuntimeException;
use ReflectionType;
use ReflectionClass;
use ReflectionProperty;

final class ParameterResolver {

     public function __construct(private Request $request) {}

     public function resolve(object $instance, string $method): array {
         $reflection = new ReflectionMethod($instance, $method);
         $params     = [];

         foreach($reflection->getParameters() as $param){
             $params = array_merge($params, $this->resolve_param($param));
         }

         return $params;
     }

     private function resolve_contract_params(RequestContract $contract): RequestContract {
         $reflection = new ReflectionClass($contract);

         foreach($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property){

             $property_name = $property->getName();
             $param = $this->resolve_param($property, false);

             if(!array_key_exists($property_name, $param)){
                 throw new RuntimeException("Missing required property: {$property_name}");
             }

             $contract->$property_name = $param[$property_name];
         }

         //run authorization + validation
         $contract->validate_and_authorize();

         return $contract;
     }

     private function resolve_param_stepwise(string $type, string $name, bool $optional, ?ReflectionType $param_type, mixed $default = null){
         $datasettings = ['name' => $name, 'type' => $param_type, 'default' => null, 'optional' => true];

         $sources = $type === 'model' ? ['input', 'session'] : ['path', 'query', 'input', 'header', 'cookie', 'session'];

         foreach($sources as $s){
             $value = new HttpDataSourceManager(new BindFrom($s, $name), ...$datasettings)->get_value();
             if($value !== null) {
                 return [$name => $value];
             }
         }

         if($optional){
             return [$name => $default];
         }

         throw new RuntimeException("Missing required parameter: {$name}");
     }

     private function resolve_param($param, $is_param = true){
         $param_name   = $param->getName();
         $param_type   = $param->getType();
         $sourceattr   = $param->getAttributes(BindFrom::class)[0] ?? null;

         /**
          * RequestContract param types must never have a BindFrom attribute on them. 
          * So complain loudly here!
          * */
         $class_name    = "";
         $is_class_type = TypeInspector::is_class_type($param_type);
         $is_contract   = false;
         if($is_class_type){
             $class_name = TypeInspector::get_class_name($param_type);

             //if its a RequestContract
             if($class_name && is_subclass_of($class_name, RequestContract::class)){
                 $is_contract = true;
                 if($sourceattr){
                     throw new RuntimeException("Request contracts cannot have a BindFrom attribute!");
                 }
             }
         }

         if($is_param){
             $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
             $optional = $param->isOptional();
         }else{
             $default = $param->hasDefaultValue() ? $param->getDefaultValue() : null;
             $optional = $param_type?->allowsNull() ?? true;
         }

         $datasettings = ['name' => $param_name, 'type' => $param_type, 'default' => $default, 'optional' => $optional];

         //at this point, if the BindFrom attribute exists, this is top priority
         if($sourceattr){
             $sourceinstance = $sourceattr->newInstance();
             $sourceinstance->set_key($param_name);
             return [$param_name => new HttpDataSourceManager($sourceinstance, ...$datasettings)->get_value()];
         }

         //if this is an object
         if($is_class_type){

             //if its a contract
             if($is_contract){
                 return [$param_name => $this->resolve_contract_params(resolve($class_name))];
             }

             //if its a model
             if($class_name && is_subclass_of($class_name, Model::class)){
                 return $this->resolve_param_stepwise('model', $param_name, $optional, $param_type, $default);
             }

             //attempt to resolve any other object types from the container
             return [$param_name => new HttpDataSourceManager(new BindFrom('di', $param_name), ...$datasettings)->get_value()];
         }

         //this is a simple param
         return $this->resolve_param_stepwise('simple', $param_name, $optional, $param_type, $default);
     }
}
