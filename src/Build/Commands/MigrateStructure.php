<?php

declare(strict_types=1);

namespace SaQle\Build\Commands;

use SaQle\Core\Support\Cli;

class MigrateStructure {
     static public function execute(){

         $root = config('framework_path');

         $destination_root = path_join([$root, 'src']);

         $core_dirs = [
             'apis',
             'auth',
             'build',
             'commons',
             'components',
             'compression',
             'core',
             'http',
             'image',
             'listeners',
             'middleware',
             'orm',
             'routes',
             'security',
             'session',
             'shortcuts',
             'templates'
         ];

         if(!is_dir($destination_root)){
             mkdir($destination_root, 0777, true);
         }

         foreach($core_dirs as $dir){
             $source = path_join([$root, $dir]);
             $destination = path_join([$destination_root, ucfirst($dir)]);

             self::migrate($source, $destination);
         }

     }

     static private function migrate(string $source, string $destination) : void {

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

                 self::migrate($source_path, $destination_path);

                 continue;
             }

             self::copy_file($source_path, $destination);
         }

         Cli::print("\n");
     }

     static private function copy_file(string $source_file, string $destination_folder) : void {
         
         Cli::print(">>>> Copying {$source_file} --> {$destination_folder}");

         if(!is_dir($destination_folder)){
             mkdir($destination_folder, 0777, true);
         }

         $filename = basename($source_file);

         $extension = pathinfo($filename, PATHINFO_EXTENSION);
         $basename  = pathinfo($filename, PATHINFO_FILENAME);

         /*
         * Rename only if the filename is entirely lowercase.
         *
         * application.php
         *      -> Application.php
         *
         * Connection.php
         *      -> Connection.php
         */

         if($basename === strtolower($basename)){
             $basename = ucfirst($basename);
         }

         $new_filename = $extension ? $basename.'.'.$extension : $basename;
         $destination_filename = path_join([$destination_folder, $new_filename]);

         Cli::print(">>>> Copying {$source_file} --> {$destination_filename}");
         copy($source_file, $destination_filename);

         Cli::print($source_file." --> ".$destination_filename);
     }
}
