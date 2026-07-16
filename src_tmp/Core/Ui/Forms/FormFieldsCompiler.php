<?php

namespace SaQle\Core\Ui\Forms;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Ui\Forms\FormBlueprint;
use SaQle\Core\Ui\Utils\Label;
use SaQle\Orm\Entities\Field\Types\{
     OneToOne, 
     OneToMany, 
     ManyToMany, 
     VirtualField, 
     Pk,
     ImageField
};
use SaQle\Orm\Entities\Field\Types\Base\RelationField;
use RuntimeException;

final class FormFieldsCompiler {

     /*private static function skip_field(object $field): bool{
         return $field instanceof OneToOne || $field instanceof OneToMany || $field instanceof ManyToMany || $field instanceof VirtualField || $field instanceof Pk;
     }*/

     private static function skip_field(object $field): bool{
         return $field instanceof VirtualField || $field instanceof Pk || $field instanceof ManyToMany || $field instanceof OneToMany;
     }

     private static function get_field_classes(array $attrs) : string {
         return match($attrs['type']){
             'select' => 'auto_form_select',
             default  => ''
         };
     }

     private static function get_field_data(array $attrs) : array {

         $data = ['field' => $attrs['name']];

         return match($attrs['type']){
             'select' => array_merge($data, []),
             default  => []
         };
     }

     public static function compile(string $model_class, bool $include_audit = false) : array {

         $model = $model_class::make();

         $model_fields = $model->table->get_clean_fields();

         $defined_field_names = $model->table->get_defined_field_names();

         $audit_field_names = $model->table->get_audit_field_names();

         $whitelisted_field_names = $defined_field_names;
         
         if($include_audit){
             $whitelisted_field_names = array_merge($whitelisted_field_names, $audit_field_names);
         }

         $pk_name = $model->get_pk_name();

         $fields = [];

         foreach($model_fields as $field){

             $name = $field->get_name();

             if(self::skip_field($field) || !in_array($name, $whitelisted_field_names) || $name === $pk_name){
                 continue;
             }

             $field_attrs = array_filter($field->get_form_field_attrs(), fn($v) => $v !== null);

             if(!$field_attrs){
                 continue;
             }
             
             $field_attrs['id'] = $field_attrs['name'];
             $field_attrs['label'] = Label::make($field_attrs['name']);
             $field_attrs['helper_text'] = $field_attrs['description'] ?? '';
             $field_attrs['value'] = '';
             $field_attrs['errors'] = [];

             $ui_type = $field instanceof ImageField ? 'image' : 'normal';

             $source = null;
             if($field instanceof RelationField){
                 if($field instanceof OneToOne){
                     $source = [
                         'model'         => $model_class,
                         'related_model' => $field->get_related_model()
                     ];

                     $field_attrs['choices'] = [];
                     $field_attrs['type'] = "select";
                     $field_attrs['uigroup'] = in_array($name, $audit_field_names) ? 'meta' : 'general';
                 }elseif($field instanceof OneToMany || $field instanceof ManyToMany){
                     $field_attrs['uigroup'] = 'relation';
                 }
             }else{
                 $field_attrs['uigroup'] = in_array($name, $audit_field_names) ? 'meta' : 'general';
             }
                 
             $form_field = new FormField($field_attrs, $ui_type);
             $form_field->class(self::get_field_classes($field_attrs));
             $form_field->data(self::get_field_data($field_attrs));
             $form_field->source($source);

             $fields[$name] = $form_field;
         }

         return $fields;
     }
}
