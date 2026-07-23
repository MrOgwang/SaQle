<?php

namespace SaQle\Console;

class CommandParser {

     /**
      * NOTE: This command parser needs to be refactored to accomodate
      * shortcut options:
      * 
      * --name (works)
      * -n (Ignored)
      * */
     public static function parse(array $argv): ParsedCommand {
         $command = $argv[1] ?? '';

         $arguments = [];
         $options = [];

         foreach(array_slice($argv, 2) as $token){
             if(str_starts_with($token, '--')){

                 $token = substr($token, 2);

                 if(str_contains($token, '=')){

                     [$key, $value] = explode('=', $token, 2);

                     $options[$key] = $value;
                 }else{

                     $options[$token] = true;
                 }

                 continue;
             }

             $arguments[] = $token;
         }

         return new ParsedCommand(
             $command,
             $arguments,
             $options,
             $argv
         );
     }
}