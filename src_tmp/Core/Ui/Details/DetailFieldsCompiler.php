<?php

namespace SaQle\Core\Ui\Details;

use SaQle\Core\Ui\AbstractFieldsCompiler;
use SaQle\Orm\Entities\Field\Types\{
     OneToMany, 
     ManyToMany, 
     TextField
};

final class DetailFieldsCompiler extends AbstractFieldsCompiler {
    
     protected static function field_data(
        object $field,
        string $name,
        array $audit_fields
     ) : array {

         $ui_group = in_array($name, $audit_fields) ? 'meta' : 
             ($field instanceof TextField ? 'description' : 'general');
             
         return [
             'ui_group' => $ui_group
         ];
     }

}
