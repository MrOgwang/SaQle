<?php

if(!function_exists('is_assoc')){
     function is_assoc(array $array): bool {
         return array_keys($array) !== range(0, count($array) - 1);
     }
}