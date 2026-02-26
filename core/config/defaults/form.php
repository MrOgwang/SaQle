<?php
namespace Acek\Config;

use Acek\Apps\Account\Models\User;

return [
     'auto_forms' => [],

     'form_field_templates' => [],

     'form_field_classes' => [
         'wrapper' => [
             'default'  => 'form_input_group',
             'valid'    => 'form_input_group',
             'invalid'  => 'form_input_group',
         ],
         'label' => [
             'default'  => 'form_input_label',
             'valid'    => 'form_input_label',
             'invalid'  => 'form_input_label',
         ],
         'helper' => [
             'default'  => 'form_input_info',
             'valid'    => 'form_input_info',
             'invalid'  => 'form_input_info',
         ],
         'control' => [
             'default'  => 'form_input_normal',
             'valid'    => 'form_input_normal',
             'invalid'  => 'form_input_normal',
         ]
     ],

     'form_input_classes' => [
         'default' => 'form_input_text',
         'text' => 'form_input_text',
         'textarea' => 'form_input_textarea',
         'select' => 'form_input_select',
         'radio' => 'form_input_radio',
         'checkbox' => 'form_input_checkbox',
         'number' => 'form_input_number',
     ]
 ]
?>