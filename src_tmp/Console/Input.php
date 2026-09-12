<?php

namespace SaQle\Console;

class Input extends Cli {

     public function ask(string $question): string {
         return trim(readline($question));
     }

     public function confirm(string $question): bool {
         return strtolower($this->ask($question.' [y/N]')) === 'y';
     }

     public function choice(string $label, array $choices){

         $this->line($label);

         $keys = array_keys($choices);
         $i = 1;

         foreach($choices as $display){
             $this->line("  {$i}. {$display}");
             $i++;
         }

         while(true){
             $selection = $this->ask("Select option: ");

             if(is_numeric($selection) && $selection >= 1 && $selection <= count($keys)){
                 return $keys[$selection - 1];
             }

             $this->error("Invalid selection.");
         }
     }

     /**
      * This will not work on a windows terminal. 
      * 
      * TO DO: Detect environment and have the appropriate
      * implementation
      * */
     public function secret(string $question): string {
         $this->line($question);
         shell_exec('stty -echo');
         $value = trim(fgets(STDIN));
         shell_exec('stty echo');
         $this->line("");

         return $value;
     }
}