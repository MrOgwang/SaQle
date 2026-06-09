<?php

namespace SaQle\Core\Ui\Forms;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Ui\Forms\FormBlueprint;
use SaQle\Orm\Entities\Field\Types\{
     OneToOne, 
     OneToMany, 
     ManyToMany, 
     VirtualField, 
     Pk,
     ImageField
};
use RuntimeException;

final class FormFieldsCompiler {

     private static function derive_label(string $name): string {
        // Replace snake_case underscores with spaces
        $label = str_replace('_', ' ', $name);

        // Split camelCase & PascalCase
        // - FooBar → Foo Bar
        // - fooBar → foo Bar
        // - APIResponse → API Response
        $label = preg_replace(
            '/(?<=\p{Ll})(?=\p{Lu})|(?<=\p{Lu})(?=\p{Lu}\p{Ll})/u',
            ' ',
            $label
        );

        // Normalize spacing
        $label = preg_replace('/\s+/', ' ', $label);

        // Title case while preserving acronyms
        $label = ucwords(strtolower($label));

        // Restore common acronyms
        $label = preg_replace_callback('/\b(Id|Api|Url|Uuid|Ip)\b/', function ($m) {
            return strtoupper($m[0]);
        }, $label);

        return trim($label);
     }

     /*private static function skip_field(object $field): bool{
         return $field instanceof OneToOne || $field instanceof OneToMany || $field instanceof ManyToMany || $field instanceof VirtualField || $field instanceof Pk;
     }*/

     private static function skip_field(object $field): bool{
         return $field instanceof VirtualField || $field instanceof Pk || $field instanceof ManyToMany || $field instanceof OneToMany;
     }

     public static function compile(string $model_class, ?FormRuntimeContext $context = null) : array {

         $model_instance = $model_class::make();
         $model_fields = $model_instance->get_fields();
         $real_fields = $model_instance->get_defined_field_names();
         $pk_name = $model_instance->get_pk_name();

         $fields = [];

         foreach($model_fields as $field){

             $name = $field->get_name();

             if(self::skip_field($field) || !in_array($name, $real_fields) || $name=== $pk_name){
                 continue;
             }

             $field_attrs = array_filter($field->get_form_field_attrs(), fn($v) => $v !== null);

             $field_attrs['id'] = $field_attrs['name'];
             $field_attrs['label'] = self::derive_label($field_attrs['name']);
             $field_attrs['helper_text'] = $field_attrs['description'] ?? '';
             $field_attrs['value'] = $context?->input[$name] ?? '';
             $field_attrs['errors'] = $context?->errors[$name] ?? [];

             if($field instanceof OneToOne){
                 $related_model = $field->get_related_model();
                 $name_property = $related_model::get_name_property() ?? [];
                 if($name_property){
                     $name_property = is_array($name_property) ? $name_property : [$name_property];
                 }
                 $pk_name = $related_model::get_pk_name();

                 $select_columns = [$pk_name];
                 if($name_property){
                     $select_columns = array_merge($select_columns, $name_property);
                 }

                 $data = $related_model::get()->select($select_columns)->all();
                 $choices = [];

                 foreach($data as $d){
                     $choices[$d->$pk_name] = $name_property ? (string)$d : $d->$pk_name;
                 }

                 $field_attrs['choices'] = $choices;
                 $field_attrs['type'] = "select";
             }

             $ui_type = $field instanceof ImageField ? 'image' : 'normal';
             $fields[$name] = new FormField($field_attrs, $ui_type);
         }

         return $fields;
     }
}
