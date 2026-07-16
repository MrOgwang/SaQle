<?php

use SaQle\Commons\Str;

if(!function_exists('snake_case')){
     function snake_case(string $value) : string {
         return Str::snake($value);
     }
}

if(!function_exists('str_plural')){
     function str_plural(string $value): string {
         return Str::plural($value);
     }
}

if(!function_exists('slugify')){
     function slugify(string $value) : string {
         return Str::slugify($value);
     }
}

if(!function_exists('natural_join')){
     function natural_join(array $items, string $conjunction = 'and', bool $use_oxford_comma = false): string {
         return Str::natural_join($items, $conjunction, $use_oxford_comma);
     }
}

