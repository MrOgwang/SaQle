<?php

namespace SaQle\Views;

use SaQle\Views\Forms\{FormControlTypes, FormControl};
use SaQle\Orm\Entities\Field\Types\{CharField, VarCharField, TinyTextField, MediumTextField, TextField, LongTextField, OneToOne, OneToMany, ManyToMany, VirtualField};
use SaQle\Orm\Entities\Field\Types\Base\Scalar;
use SaQle\Views\Forms\Controls\FormControlFactory;

class AutoForm{
     public function generate_form(array $attributes, array $modelmap){
         $formname  = $attributes['name'] ?? '';
         $modelname = $attributes['model']; //the model must exist. throw an exception here!
         $mode      = $attributes['mode'];
         $model     = $modelmap[$modelname] ?? ''; 

         if(!$model || !class_exists($model)){
             return "";
         }

         $formview = "<form>";

         echo "Model found: $model\n";
         $modelinstance = $model::state();

         $fields = $modelinstance->meta->fields;

         foreach($fields as $f){

             if($f instanceof OneToOne || $f instanceof OneToMany || $f instanceof ManyToMany || $f instanceof VirtualField)
                 continue;

              $control_def = $f->get_control_kwargs();
              //remove keys with null values
              $control_def = array_filter($control_def, fn($value) => $value !== null);

              $factory = new FormControlFactory();
              $template = $factory->create($control_def)->render();

              $formview .= $template;
         }

         $formview .= "</form>"; 

         return $formview;
     }
}