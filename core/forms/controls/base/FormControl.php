<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls\Base;

use SaQle\Core\Forms\Controls\Interfaces\IFormControl;
use ReflectionObject;

abstract class FormControl implements IFormControl{
     /*Global attributes */
     public ?string $id = null;
     public ?string $name = null;
     public ?string $class = null;
     public ?string $style = null;
     public ?string $title = null;

     /* State */
     public bool $required = false;
     public bool $readonly = false;
     public bool $disabled = false;

     /* Data / aria */
     public array $data = [];
     public array $aria = [];

     protected array $exclude_from_render = [
         'exclude_from_render',
         'options',
         'value',
         'type',
     ];

     public function __construct(array $attributes = []) {
        $this->hydrate($attributes);
     }

     protected function hydrate(array $attributes): void {
         foreach ($attributes as $key => $value) {
             if (property_exists($this, $key)) {
                 $this->{$key} = $value;
             }
         }
     }

     protected function render_attributes(): string {
         $html = [];

         $ref  = new ReflectionObject($this);

         foreach ($ref->getProperties() as $prop) {
             $prop->setAccessible(true);
             $name  = $prop->getName();
             $value = $prop->getValue($this);

             //Skip non-HTML internals
             if(in_array($name, $this->exclude_from_render, true)) {
                 continue;
             }

             //data-* attributes
             if ($name === 'data' && is_array($value)) {
                 foreach ($value as $k => $v) {
                     $html[] = 'data-'.$k.'="' . htmlspecialchars((string)$v) . '"';
                 }
                 continue;
             }

             //aria-* attributes
             if ($name === 'aria' && is_array($value)) {
                 foreach ($value as $k => $v) {
                     $html[] = 'aria-' . $k . '="' . htmlspecialchars((string)$v) . '"';
                 }
                 continue;
             }

             //transform the name
             $name = str_replace('_', '-', $name);

             //Boolean attributes
             if (is_bool($value)) {
                 if ($value === true) {
                     $html[] = $name;
                 }
                 continue;
             }

             //Normal attributes
             if ($value !== null) {
                 if(is_array($value)){
                     $value = implode(', ', $value);
                 }
                 $html[] = $name.'="'.htmlspecialchars((string)$value) . '"';
             }
         }

         return implode(' ', $html);
     }
}
