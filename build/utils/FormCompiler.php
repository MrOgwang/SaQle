<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Views\AutoForm;
use RuntimeException;

final class FormCompiler {

     private static function cache_form(string $form_template, string $form_name){
         //write to the cache file
         $folder = path_join([config('base_path'), config('forms_cache_dir')]);
         if(!is_dir($folder)){
             mkdir($folder, 0777, true);
         }

         $file_name = path_join([$folder, $form_name.".".config('component_template_ext')]);
         file_put_contents($file_name, $form_template);
     }

     public static function compile(){
         $auto_forms = config('auto_forms');
         if(!$auto_forms)
             return;

         //auto forms must be an associative array, where the keys are model classes
         Assert::isNonEmptyMap($auto_forms, "Invalid auto forms configuration!");

         foreach($auto_forms as $model_class => $forms){
             //model_class must be a valid model
             if(!class_exists($model_class) || !is_subclass_of($model_class, Model::class)){
                 throw new RuntimeException(
                     "The model {$model_class} is not defined or is not a model!"
                 );
             }

             /**
              * forms must be an associative array where
              * 
              * key   => the form name
              * value => a config array
              * */
             Assert::isNonEmptyMap($forms, "Invalid auto forms configuration!");

             foreach($forms as $form_name => $form_config){
                 /**
                  * the form configuration must be an associative array must contain a key called mode
                  * 
                  * mode => the action to perform(create, update)
                  * */
                 Assert::isNonEmptyMap($form_config, "Invalid auto forms configuration!");

                 if(!array_key_exists('mode', $form_config) || !in_array($form_config['mode'], ['create', 'update'])){
                     throw new RuntimeException("Invalid auto forms configuration!");
                 }

                 //now generate forms here.
                 $form = (new AutoForm())->generate([
                     'name'  => $form_name,
                     'mode'  => $form_config['mode'],
                     'model' => $model_class
                 ]);

                 //cache the generated form.
                 self::cache_form($form, $form_name);
             }
         }
     }
}
