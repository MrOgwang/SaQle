<?php

namespace SaQle\Core\Ui\Panels;

use SaQle\Core\Ui\AbstractFieldsCompiler;
use SaQle\Orm\Entities\Field\Types\ImageField;

final class PanelFieldsCompiler extends AbstractFieldsCompiler {

     protected static function field_data(
         object $field,
         string $name,
         array $audit_fields
     ) : array {
         return [
             'ui_type' => $field instanceof ImageField ? 'image' : 'normal',
         ];
     }
     
}
