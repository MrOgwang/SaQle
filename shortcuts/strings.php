<?php

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