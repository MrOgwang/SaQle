<?php

namespace SaQle\Core\Support;

final class RuleParser {

     /**
     * Converts rule strings into structured rules.
     *
     * Rules:
     * 1. No threshold → true
     * 2. 'choices' with comma-separated values → array
     * 3. Everything else → raw threshold string
     */
     public static function parse(array $rules): array {
         $parsed = [];

         foreach($rules as $rule_string) {

             //split only on first colon
             $parts = explode(':', $rule_string, 2);

             $rule_name = trim($parts[0]);

             //If no threshold specified → bool true
             if(!isset($parts[1]) || trim($parts[1]) === ''){
                 $parsed[$rule_name] = true;
                 continue;
             }

             $threshold = trim($parts[1]);

             //Special handling for choices
             if($rule_name === 'choices'){
                 $parsed[$rule_name] = array_map(
                    'trim',
                    explode(',', $threshold)
                 );
                 continue;
             }

             //Everything else → raw threshold
             $parsed[$rule_name] = $threshold;
         }

         return $parsed;
    }
}