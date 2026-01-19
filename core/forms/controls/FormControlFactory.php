<?php
declare(strict_types = 1);

namespace SaQle\Core\Forms\Controls;

use Exception;
use SaQle\Core\Forms\Controls\Base\FormControl;

class FormControlFactory{
     protected static array $map = [
         'checkbox'       => CheckboxInputControl::class,
         'color'          => ColorInputControl::class,
         'date'           => DateInputControl::class,
         'datetime-local' => DateTimeInputControl::class,
         'email'          => EmailInputControl::class,
         'file'           => FileInputControl::class,
         'hidden'         => HiddenInputControl::class,
         'month'          => MonthInputControl::class,
         'number'         => NumberInputControl::class,
         'password'       => PasswordInputControl::class,
         'radio'          => RadioInputControl::class,
         'range'          => RangeInputControl::class,
         'search'         => SearchInputControl::class,
         'select'         => SelectControl::class,
         'tel'            => TelephoneInputControl::class,
         'textarea'       => TextareaControl::class,
         'text'           => TextInputControl::class,
         'time'           => TimeInputControl::class,
         'url'            => UrlInputControl::class,
         'week'           => WeekInputControl::class
     ];

     public static function create(array $definition): FormControl {
         $type = $definition['type'] ?? 'text';
         unset($definition['type']);

         if(!isset(self::$map[$type])) {
             throw new Exception("Unsupported control type: {$type}");
         }

         return new self::$map[$type]($definition);
     }
}
