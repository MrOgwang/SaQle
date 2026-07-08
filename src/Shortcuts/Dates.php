<?php
if(!function_exists('format_date')){
     function format_date(mixed $input, string $format) : string {
         if(is_numeric($input)){
             $date = new DateTime();
             $date->setTimestamp($input);
         }else{
             $date = new DateTime($input);
         }

         return $date->format($format);
     }
}