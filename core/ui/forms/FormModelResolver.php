<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

use SaQle\Orm\Entities\Model\Schema\Model;
use RuntimeException;
use ReflectionClass;

class FormModelResolver {
	 public static function resolve(string $form_name) : array {

         $models_cache_path = path_join([config('base_path'), config('class_mappings_dir'), "models.php"]);
         if(!file_exists($models_cache_path)){
             throw new RuntimeException("Models cache path: {$models_cache_path}, not found!");
         }

         $models = require $models_cache_path;

	 	 $parts = explode('.', $form_name);

         if(count($parts) === 3){
             [$module, $model, $method] = $parts;
         }elseif(count($parts) === 2){
             [$model, $method] = $parts;
             $module = "";
         }else{
             throw new RuntimeException("Invalid form name format.");
         }

         $model_key = $module ? $module.".".$model : $model;

         $model_class = $models[$model_key] ?? null;

         if(!$model_class){
             throw new RuntimeException("No model class found in file.");
         }

         if(!method_exists($model_class, $method)){
             throw new RuntimeException("Method '{$method}' not found in model '{$model_class}'.");
         }

         return [$model, $model_class, $method];
	 }
}