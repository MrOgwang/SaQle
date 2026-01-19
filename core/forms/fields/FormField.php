<?php

namespace SaQle\Core\Forms\Fields;

use SaQle\Core\Forms\Controls\Base\FormControl;
use SaQle\Core\Forms\Controls\FormControlFactory;
use SaQle\Core\Ui\View;
use RuntimeException;

class FormField {
     protected FormControl $control;
     protected array       $field_attributes;
     protected string      $state;
     private   array       $field_classes;

     public function __construct(array $control_attributes, array $field_attributes = [], string $state = 'default'){
         $this->state = $state;
         $this->field_classes = $this->extract_field_classes();

         //add an id
         $control_attributes['id'] ??= $control_attributes['name'] ?? '';
         //add classes
         $control_attributes['class'] = $this->field_classes['control_classes'];
         //more control classes
         $ctrl_classes = config('form_input_classes', []);

         if(array_key_exists($control_attributes['type'], $ctrl_classes)){
             $control_attributes['class'] = $control_attributes['class']." ".$ctrl_classes[$control_attributes['type']];
         }else{
            $control_attributes['class'] = $control_attributes['class']." ".$ctrl_classes['default'] ?? '';
         }

         $this->control = (new FormControlFactory())->create($control_attributes);

         //make sure template and label exists
         if(!isset($field_attributes['label'])){
             throw new RuntimeException("The label for this input field has not been provided!");
         }

         if(!isset($field_attributes['template']) || !file_exists($field_attributes['template'])){
             throw new RuntimeException("The template or this input field does not exist!");
         }

         $this->field_attributes = $field_attributes;
     }

     private function interpolate(string $template, array $context): string {
         $view = new View($template, false);
         $view->set_context($context);

         return $view->view();
     }

     private function extract_field_classes() : array {
         $config = config('form_field_classes', []);
         
         if(!$config){
             return [
                'wrapper_classes' => '',
                'label_classes' => '',
                'helper_classes' => '',
                'control_classes' => ''
             ];
         }

         $parts = ['wrapper', 'helper', 'label', 'control'];
         $classes = [];

         foreach($parts as $part){
             if(!isset($config[$part])){
                 $classes[$part."_classes"] = '';
                 continue;
             }

             if(!isset($config[$part][$this->state])){
                 $classes[$part."_classes"] = '';
                 continue;
             }

             $classes[$part."_classes"] = $config[$part][$this->state];
         }

         return $classes;
     }

     public function render(array $runtime) {
         $template = file_get_contents($this->field_attributes['template']);
         unset($this->field_attributes['template']);

         unset($this->field_classes['control_classes']);

         $context = array_merge(
             $this->field_attributes,
             $runtime,
             [
                'id'        => $this->control->id ?? null,
                'name'      => $this->control->name ?? null,
                'required'  => $this->control->required ?? false,
                'control'   => $this->control->render(),
                'helper_id' => $this->control->id ? $this->control->id."_help" : ''
             ],
             $this->field_classes
         );

         return $this->interpolate($template, $context);
     }
}
