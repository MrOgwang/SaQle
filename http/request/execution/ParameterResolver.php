<?php

namespace SaQle\Http\Request\Execution;

use ReflectionMethod;
use SaQle\Http\Request\Request;
use SaQle\Http\Request\Data\Sources\From;
use SaQle\Auth\Models\Attributes\AuthUser;
use SaQle\Http\Request\Data\Sources\Managers\HttpDataSourceManager;

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

     private function resolve_param($param){
         $param_name   = $param->getName();
         $param_type   = $param->getType();
         $param_type   = !is_null($param_type) ? str_replace('?', '', $param_type) : $param_type;
         $default_val  = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
         $optional     = $param->isOptional();
         $sourceattr   = $param->getAttributes()[0] ?? null;
         $attrinstance = $sourceattr ? $sourceattr->newInstance() : null;

         if($attrinstance){
             if($attrinstance instanceof From){
                 return [$param_name => new HttpDataSourceManager(
                     $attrinstance, ...['name' => $param_name, 'type' => $param_type, 'default' => $default_val, 'optional' => $optional]
                 )->get_value()];
             }

             if($attrinstance instanceof AuthUser){
                 return [$param_name => $this->request->user];
             }
         }

         if($param_type && class_exists($param_type)){
             return [$param_name = resolve($param_type)];
         }

         //check route params, then query, then data
         $value = $this->request->params->get($param_name);
         if(!is_null($value)){
             return [$param_name = $value];
         }

         $value = $this->request->queries->get($param_name);
         if(!is_null($value)){
             return [$param_name = $value];
         }

         $value = $optional ? $this->request->data->get($param_name, $default_val) : $this->request->data->get_or_fail($param_name);

         return [$param_name = $value];
     }
}
