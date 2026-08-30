<?php

declare(strict_types=1);

namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;
use SaQle\Console\{
     Command, 
     CommandContext
};
use SaQle\Console\Signature\Signature;

class MigrateProject {

     public function signature(): Signature {
         return Signature::make();
     }

     public function handle(CommandContext $context) : int {

         $root = path_join([config('base_path'), 'src_tmp']);

         $destination_root = path_join([config('base_path'), 'src']);

         $core_dirs = [
             'authorization',
             'bootstrap',
             'components',
             'config',
             'databases',
             'env',
             'logs',
             'middlewares',
             'modules',
             'public',
             'routes',
             'services'
         ];

         if(!is_dir($destination_root)){
             mkdir($destination_root, 0777, true);
         }

         foreach($core_dirs as $dir){
             $source = path_join([$root, $dir]);
             $destination = path_join([$destination_root, ucfirst($dir)]);

             $this->migrate($source, $destination);
         }

         return 0;

     }

     private function migrate(string $source, string $destination) : void {

         Cli::print("Migrating {$source} --> {$destination}");

         $items = scandir($source);

         foreach($items as $item){
             if($item === '.' || $item === '..'){
                 continue;
             }

             $source_path = path_join([$source, $item]);

             if(is_dir($source_path)){
                 
                 $new_folder = ucfirst($item);

                 $destination_path = path_join([$destination, $new_folder]);

                 if(!is_dir($destination_path)){
                     mkdir($destination_path, 0777, true);
                 }

                 $this->migrate($source_path, $destination_path);

                 continue;
             }

             $this->copy_file($source_path, $destination);
         }

         Cli::print("\n");
     }

     private function get_new_filename(string $source_file, string $basename, string $extension){

         $new_filename = "";

         if(strtolower($extension) === 'php'){
             $code = file_get_contents($source_file);
             $tokens = token_get_all($code);

             $declarations = [];

             $count = count($tokens);

             //Token types we want to detect.
             $declaration_tokens = [
                 T_CLASS,
                 T_INTERFACE,
                 T_TRAIT,
             ];

             //T_ENUM exists only on PHP 8.1+
             if(defined('T_ENUM')){
                 $declaration_tokens[] = T_ENUM;
             }

             for($i = 0; $i < $count; $i++){
                 if(!is_array($tokens[$i])){
                     continue;
                 }

                 if(!in_array($tokens[$i][0], $declaration_tokens, true)){
                     continue;
                 }

                 //Skip whitespace/comments until we reach the class name
                 $j = $i + 1;

                 while($j < $count){
                     if(!is_array($tokens[$j])){
                         $j++;
                         continue;
                     }

                     if($tokens[$j][0] === T_WHITESPACE || $tokens[$j][0] === T_COMMENT || $tokens[$j][0] === T_DOC_COMMENT){
                         $j++;
                         continue;
                     }

                     if($tokens[$j][0] === T_STRING){
                         $declarations[] = $tokens[$j][1];
                     }

                     break;
                 }
             }

             if(count($declarations) === 1){
                 //Rename to match the declared type.
                 $new_filename = $declarations[0];
             }elseif (count($declarations) > 1) {
                 Cli::print(">>>> Multiple classes found in: {$source_file}");

                 //Leave filename as-is (or ucfirst if lowercase).
                 $new_filename = ($basename === strtolower($basename)) ? ucfirst($basename) : $basename;
             }else{
                 //No classes found.
                 $new_filename = ($basename === strtolower($basename)) ? ucfirst($basename) : $basename;
             }
         }else{
             //Non-PHP file.
             $new_filename = ($basename === strtolower($basename)) ? ucfirst($basename) : $basename;
         }

         return $new_filename;
     }

     private function copy_file(string $source_file, string $destination_folder) : void {
         
         Cli::print(">>>> Copying {$source_file} --> {$destination_folder}");

         if(!is_dir($destination_folder)){
             mkdir($destination_folder, 0777, true);
         }

         $filename = basename($source_file);

         $extension = pathinfo($filename, PATHINFO_EXTENSION);
         $basename  = pathinfo($filename, PATHINFO_FILENAME);

         //determine new file name.
         $basename = $this->get_new_filename($source_file, $basename, $extension);

         $new_filename = $extension ? $basename.'.'.$extension : $basename;
         $destination_filename = path_join([$destination_folder, $new_filename]);

         Cli::print(">>>> Copying {$source_file} --> {$destination_filename}");
         copy($source_file, $destination_filename);

         Cli::print($source_file." --> ".$destination_filename);
     }
}
