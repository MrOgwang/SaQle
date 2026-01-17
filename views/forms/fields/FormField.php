<?php

namespace SaQle\Views\Forms\Fields;

use SaQle\Views\Forms\Controls\Base\FormControl;
use RuntimeException;

class FormField {
      protected FormControl $control;
      protected string      $label;
      protected ?string     $helper_text;
      protected array       $attributes;
      protected string      $template;

      public function __construct(
         FormControl $control, 
         string $label, 
         string $template,
         string $helper_text = '',
         array  $attributes = [],
     ){
         $this->control = $control;
         $this->label = $label;
         $this->template = $template;
         $this->helper_text = $helper_text;
         $this->attributes = $attributes;
         
         //make sure template and label exists
         if(!$label){
             throw new RuntimeException("The label for this input field has not been provided!");
         }

         if(!file_exists($this->template)){
             throw new RuntimeException("The template or this input field does not exist!");
         }
     }

     public function render(){
         $input_control_template = $this->control->render();
        [
            'wrapper_classes'     => [],
            'name'                => '',
            'id'                  => '',
            'label_classes'       => [],
            'helper_text'         => '',
            'helper_text_id'      => '',
            'helper_text_classes' => [],
            'input_control'       => '',

            'control_classes'     => [],
            'control_attributes'  => []
        ]
     }
}
