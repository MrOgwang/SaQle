<?php

namespace SaQle\Views;

use SaQle\Views\Forms\{FormControlTypes, FormControl};
use SaQle\Orm\Entities\Field\Types\{CharField, VarCharField, TinyTextField, MediumTextField, TextField, LongTextField, OneToOne, OneToMany, ManyToMany, VirtualField};
use SaQle\Orm\Entities\Field\Types\Base\Scalar;
use SaQle\Views\Forms\Controls\FormControlFactory;
use SaQle\Views\Forms\Fields\FormField;

class AutoForm{
     public function generate(array $attributes){
         $formname  = $attributes['name'];
         $mode      = $attributes['mode'];
         $model     = $attributes['model'];

         $formview = "<form>";

         $modelinstance = $model::state();

         $fields = $modelinstance->meta->fields;

         $field_templates = config('form_field_templates', []);

         foreach($fields as $f){

             if($f instanceof OneToOne || $f instanceof OneToMany || $f instanceof ManyToMany || $f instanceof VirtualField)
                 continue;

              $control_def = $f->get_control_kwargs();

              print_r($control_def);
              
              //remove keys with null values
              $control_def = array_filter($control_def, fn($value) => $value !== null);

              $form_field = new FormField(
                  control: (new FormControlFactory())->create($control_def),
                  label: $control_def['name'],
                  template: $field_templates[$control_def['type']] ?? ($field_templates['default'] ?? __DIR__.'/templates/field.html'),
                  helper_text: $control_def['description'] ?? ''
              );

              $template = $form_field->render();

              $formview .= $template;
         }

         $formview .= "</form>"; 

         return $formview;
     }
}