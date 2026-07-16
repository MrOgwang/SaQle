<?php

namespace SaQle\Core\Ui;

use SaQle\Core\Ui\Utils\Label;
use SaQle\Orm\Entities\Field\Types\{
    OneToOne,
    OneToMany,
    ManyToMany,
    VirtualField,
    Pk
};

abstract class AbstractFieldsCompiler {
     protected static function skip_field(object $field): bool {
         return $field instanceof VirtualField
             || $field instanceof Pk
             || $field instanceof ManyToMany
             || $field instanceof OneToMany;
     }

     public static function compile(string $model_class): array {
         $model = $model_class::make();

         $defined = $model->table->get_defined_field_names();
         $audit   = $model->table->get_audit_field_names();

         $allowed = array_merge($defined, $audit);
         $pk      = $model->get_pk_name();

         $fields = [];

         foreach ($model->table->get_clean_fields() as $field) {

             $name = $field->get_name();

             if(static::skip_field($field) || !in_array($name, $allowed) || $name === $pk){
                 continue;
             }

             $fields[$name] = (object) array_merge(
                 [
                     'is_fk' => $field instanceof OneToOne,
                     'label' => Label::make($name),
                 ],
                 static::field_data($field, $name, $audit)
             );
         }

         return $fields;
     }

     abstract protected static function field_data(
         object $field,
         string $name,
         array $audit_fields
     ) : array;
}