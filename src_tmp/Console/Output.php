<?php

namespace SaQle\Console;

class Output extends Cli {

     public function table(array $headers, array $rows) : void {
         $widths = [];

         //Determine column widths
         foreach($headers as $i => $header){
             $widths[$i] = strlen($header);

             foreach($rows as $row){
                $widths[$i] = max($widths[$i], strlen((string)$row[$i]));
             }
         }

         //Header
         foreach ($headers as $i => $header) {
             echo str_pad($header, $widths[$i] + 2);
         }
         echo PHP_EOL;

         //Separator
         foreach ($widths as $width) {
             echo str_repeat('-', $width) . '  ';
         }
         echo PHP_EOL;

         // Rows
         foreach ($rows as $row) {
             foreach ($row as $i => $cell) {
                 echo str_pad((string)$cell, $widths[$i] + 2);
             }
             echo PHP_EOL;
         }
     }

     public function field(string $label, mixed $value): void {
        
         if($value === null || $value === ''){
             $value = '—';
         }

         if(is_bool($value)){
             $value = $value ? 'Yes' : 'No';
         }

         $this->line(str_pad($label, 20).$value);
     }

}