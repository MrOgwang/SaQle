<?php

use SaQle\Commons\StringUtils;

if(!function_exists('snake_case')){
     function snake_case(string $value) : string {
         return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
     }
}

if(!function_exists('str_plural')){
     function str_plural(string $value): string {
         //Simple pluralizer: Add 's' or handle irregulars (expand as needed, e.g., 'person' => 'people').
         return $value . 's';
     }
}

if(!function_exists('slugify')){
     function slugify(string $value) : string {
         $instance = new class {
             use StringUtils;
         };

         return $instance::slugify($value);
     }
}

/**
 * Joins an array of strings into a human-readable list using a natural language conjunction.
 *
 * @param array<string> $items The array of strings to join.
 * @param string $conjunction The final separator (e.g., 'and', 'or', 'vs'). Defaults to 'and'.
 * @param bool $use_oxford_comma Whether to include a comma before the conjunction for 3+ items. Defaults to false.
 * @return string
 */
if(!function_exists('implode_natural')){
     function implode_natural(array $items, string $conjunction = 'and', bool $use_oxford_comma = false): string {
         $count = count($items);

         if($count === 0){
             return '';
         }
        
         if($count === 1){
             return $items[0];
         }

         $last_item = array_pop($items);
         $glue = implode(', ', $items);
        
         if($use_oxford_comma && $count > 2){
             $glue .= ',';
         }

         return $glue.' '.$conjunction.' '.$last_item;
     }
}

