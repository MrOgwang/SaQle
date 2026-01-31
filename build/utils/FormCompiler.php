<?php

namespace SaQle\Build\Utils;

use SaQle\Core\Assert\Assert;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Forms\FormBlueprint;
use SaQle\Orm\Entities\Field\Types\{OneToOne, OneToMany, ManyToMany, VirtualField, Pk};
use RuntimeException;

final class FormCompiler {

     private static function cache_blueprints(array $blueprints){
         //write to the cache file
         $folder = path_join([config('base_path'), config('forms_cache_dir')]);
         if(!is_dir($folder)){
             mkdir($folder, 0777, true);
         }

         foreach($blueprints as $name => $blueprint){
             $file_name = path_join([$folder, $name.".php"]);

             $contents =
                "<?php\n".
                "declare(strict_types=1);\n\n".
                "return ".var_export($blueprint, true).";\n";

             file_put_contents($file_name, $contents);
         }
     }

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

     public static function compile(){
         $auto_forms = config('auto_forms');
         $field_templates = config('form_field_templates', []);

         if(!$auto_forms)
             return;

         //auto forms must be an associative array, where the keys are model classes
         Assert::isNonEmptyMap($auto_forms, "Invalid auto forms configuration!");

         $blueprints = [];

         foreach($auto_forms as $model_class => $forms){
             //model_class must be a valid model
             if(!class_exists($model_class) || !is_subclass_of($model_class, Model::class)){
                 throw new RuntimeException(
                     "The model {$model_class} is not defined or is not a model!"
                 );
             }

             /**
              * forms is an array of form names. The form names are public methods defined on the model
              * 
              * Make sure forms in non empty string array
              * */
             Assert::allStringNotEmpty($forms, "Invalid auto forms configuration!");

             $model_instance = $model_class::make();
             $model_fields = $model_instance->get_fields();
             $real_fields = $model_instance->get_defined_field_names();
             $pk_name = $model_instance->get_pk_name();

             foreach($forms as $form_name){
                 //check that the form_name is a method defined in model
                 if(!method_exists($model_class, $form_name)){
                     throw new RuntimeException("The form: {$form_name} has not been defined in model: {$model_class}!");
                 }

                 $form_config = $model_instance->$form_name();

                 /**
                  * the form configuration must be an associative array must contain a key called mode
                  * 
                  * mode => the action to perform(create, update)
                  * */
                 Assert::isNonEmptyMap($form_config, "Invalid auto forms configuration!");

                 if(!array_key_exists('mode', $form_config) || !in_array($form_config['mode'], ['create', 'update'])){
                     throw new RuntimeException("Invalid auto forms configuration!");
                 }

                 $fields = [];
                 $fillables = $form_config['fillable'] ?? [];

                 foreach ($model_fields as $field){
                     if (self::skip_field($field) || 
                         !in_array($field->field_name, $real_fields) || 
                         $field->field_name === $pk_name ) {
                         continue;
                     }

                     if($fillables && !in_array($field->field_name, $fillables)){
                         continue;
                     }

                     $control_attrs = array_filter($field->get_control_kwargs(), fn($v) => $v !== null);

                     $template = $field_templates[$control_attrs['type']] ?? ($field_templates['default'] ?? realpath(__DIR__.'/../../templates/fields/default.html'));
                     if(!$template){
                         throw new RuntimeException("Invalid auto forms configuration. A field template was not provided!");
                     }

                     $fields[] = [
                         'name'          => $control_attrs['name'],
                         'type'          => $control_attrs['type'],
                         'control_attrs' => $control_attrs,
                         'field_attrs'   => [
                             'label'        => self::derive_label($control_attrs['name']),
                             'template'     => $template,
                             'helper_text'  => $control_attrs['description'] ?? ''
                        ]
                     ];
                 }

                 $blueprints[$form_name] = new FormBlueprint(
                     name: $form_name,
                     model_class: $model_class,
                     mode: $form_config['mode'],
                     auto_wire: $form_config['auto_wire'],
                     fields: $fields
                 )->get();
             }
         }

         //cache the generated blue prints
         self::cache_blueprints($blueprints);
     }
}
