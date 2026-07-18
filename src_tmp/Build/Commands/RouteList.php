<?php
namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;
use SaQle\Core\Registries\RouteRegistry;

class RouteList {

     public static function execute(){

         $compiled_routes = RouteRegistry::all();

         $headers = ['#', 'METHOD', 'URI', 'NAME', 'COMPONENT'];

         $rows = [];

         $count = 0;
         foreach($compiled_routes as $route){

             $count += 1;

             $rows[] = [
                 "#{$count}",
                 $route['method'],
                 $route['route']['url'],
                 $route['route']['name'],
                 $route['route']['target']
             ];
         }

         self::table($headers, $rows);
     }

     private static function table(array $headers, array $rows) : void {
         $widths = [];

         //Determine column widths
         foreach($headers as $i => $header){
             $widths[$i] = strlen($header);

             foreach($rows as $row){
                $widths[$i] = max($widths[$i], strlen((string)$row[$i]));
             }
         }

        // Header
        foreach ($headers as $i => $header) {
            echo str_pad($header, $widths[$i] + 2);
        }
        echo PHP_EOL;

        // Separator
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
}
