<?php
namespace SaQle\Lib\Components\ResourceCreate;

use SaQle\Core\Components\ComponentDefinition;
use SaQle\Core\Ui\Forms\{
     FormMode, 
     FormContext
};
use SaQle\Routing\Resources\ResourceRouteUtils;
use RuntimeException;

final class Definition extends ComponentDefinition {

     use ResourceRouteUtils;

     protected function init() : void {

         $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";

         $form = $this->create_auto_form(FormMode::CREATE, $this->props['name'] ?? null);
         
         //$form->bind(FormContext::make(), request());

         if(!$form){
             throw new RuntimeException("Unknown resource form requested!");
         }

         $this->props['form'] = $form;
         $this->props['resource'] = $this->resource($model_class);
         $this->props['model'] = $model_class;

     }

}