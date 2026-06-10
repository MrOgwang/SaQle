<?php

namespace SaQle\Core\Support;

class Cli {
	 public static function print(string $message){
         fwrite(STDERR, $message.PHP_EOL);
     }

     public static function read(string $label) : string {
         return trim(readline($label));
     }

     public static function choice(string $label, array $choices){
         echo $label.PHP_EOL;

         $keys = array_keys($choices);
         $i = 1;

         foreach($choices as $display){
             echo "  {$i}. {$display}" . PHP_EOL;
             $i++;
         }

         while(true){
             $selection = trim(self::read("Select option: "));

             if(is_numeric($selection) && $selection >= 1 && $selection <= count($keys)){
                 return $keys[$selection - 1];
             }

             echo "Invalid selection.".PHP_EOL;
         }
     }
}