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
     Pk
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

     private static function skip_field(object $field): bool{
         return $field instanceof OneToOne || $field instanceof OneToMany || $field instanceof ManyToMany || $field instanceof VirtualField || $field instanceof Pk;
     }

     public static function compile(string $model_class) : array {

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

             $fields[$name] = new FormField($field_attrs);
         }

         return $fields;
     }
}
