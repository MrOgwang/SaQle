<?php
declare(strict_types=1);

namespace SaQle\Core\Forms;

use SaQle\Core\Forms\Fields\FormField;
use SaQle\Auth\Models\GuestUser;

class AutoForm {
     private FormBlueprint $blueprint;
     private FormRuntimeContext $context;

     public function __construct(FormBlueprint $blueprint) {
         $this->blueprint = $blueprint;
     }

     public static function from_blueprint(array $config){
         $blueprint = new FormBlueprint(
             name:        $config['name'],
             model_class: $config['model_class'],
             mode:        $config['mode'],
             auto_wire:   $config['auto_wire'],
             fields:      $config['fields'],
         );
         return new static($blueprint);
     }

     public function bind(FormRuntimeContext $context): void {
         $this->context = $context;
     }

     public function render(): string {
         $html = '<form method="'.$this->context->method.'" action="'.$this->context->action.'">';

         $context = ['session_user' => request()->user ?? new GuestUser()];

         foreach ($this->blueprint->fields as $field_def){

             $control_attrs = $field_def['control_attrs'];

             if (isset($this->context->input[$field_def['name']])) {
                 $control_attrs['value'] = $this->context->input[$field_def['name']];
             }

             $field = new FormField(
                 control_attributes: $control_attrs,
                 field_attributes: array_merge($context, $field_def['field_attrs']),
                 state: request()->method() === 'GET' ? 'default' : ($this->context->errors ? 'invalid' : 'valid')
             );

             $html .= $field->render([
                 'error' => $this->context->errors[$field_def['name']] ?? null
             ]);
         }

         $html .= '</form>';

         return $html;
     }
}