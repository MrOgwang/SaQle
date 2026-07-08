<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Support\Cli;

trait MakeUserUtils {
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

     private function collect_user_data(string $model_class) : array {
         $user_properties = [];

         $model_fields = $model_class::get_fields();
         $pk_name = $model_class::get_pk_name();
         $defined_field_names = $model_class::get_defined_field_names();

         foreach($defined_field_names as $name){
             if($name === $pk_name){
                 continue;
             } 

             $field = $model_fields[$name];
             $native_type = $field->get_native_type();

             $label = $this->derive_label($name);
             if($field instanceof CharChoiceField || $field instanceof IntegerChoiceField){
                 $value = Cli::choice($label, $field->get_raw_choices());
             }else{
                 $value = Cli::read($label.": ");
             }

             if($native_type){
                 $value = match($native_type){
                     'string'  => (string)$value,
                     'integer' => (int)$value,
                     'float'   => (float)$value,
                     default   => $value
                 };
             }

             $user_properties[$name] = $value;
             Cli::print("");
         }

         return $user_properties;
     }
}