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
use SaQle\Core\Support\BindFrom;
use SaQle\Http\Request\Data\Sources\Managers\HttpDataSourceManager;
use SaQle\Orm\Entities\Model\Schema\Model;
use RuntimeException;
use ReflectionType;

final class ParameterResolver {

     public function __construct(private Request $request) {}

     public function resolve(object $instance, string $method): array {
         $reflection = new ReflectionMethod($instance, $method);
         $params     = [];

         foreach ($reflection->getParameters() as $param){
             $params = array_merge($params, $this->resolve_param($param));
         }

         return $params;
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

     private function resolve_param($param){
         $param_name   = $param->getName();
         $param_type   = $param->getType();
         $default      = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
         $optional     = $param->isOptional();
         $sourceattr   = $param->getAttributes(BindFrom::class)[0] ?? null;
         $datasettings = ['name' => $param_name, 'type' => $param_type, 'default' => $default, 'optional' => $optional];

         //if the BindFrom attribute exists, this is top priority
         if($sourceattr){
             $sourceinstance = $sourceattr->newInstance();
             $sourceinstance->set_key($param_name);
             return [$param_name => new HttpDataSourceManager($sourceinstance, ...$datasettings)->get_value()];
         }

         //if this is an object
         if(TypeInspector::is_class_type($param_type)){
             $class_name = TypeInspector::get_class_name($param_type);

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
