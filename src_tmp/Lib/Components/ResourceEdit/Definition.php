<?php
namespace SaQle\Lib\Components\ResourceEdit;

use SaQle\Core\Components\Abstracts\AbstractDefinition;
use SaQle\Core\Ui\Forms\{
     FormMode, 
     FormContext
};
use SaQle\Routing\Resources\ResourceRouteUtils;
use RuntimeException;

final class Definition extends AbstractDefinition {

     use ResourceRouteUtils;

     protected function initialize(array $props = []) : void {

         parent::initialize($props);

         $model_parts = explode("@", request()->route->model_class);
         $model_class = $model_parts[0] ?? "";

         $form = $this->create_auto_form(FormMode::UPDATE, $this->props['name'] ?? null);

         //$form->bind(FormContext::make($object), request());

         if(!$form){  
             throw new RuntimeException("Unknown resource form requested!");
         }

         $this->context()->props()->set('form', $form);
         $this->context()->props()->set('resource', $this->resource($model_class));
         $this->context()->props()->set('model', $model_class);
     }

}