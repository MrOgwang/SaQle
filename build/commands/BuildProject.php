<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\{
     Manifest, 
     ComponentCompiler,
     RouteCompiler, 
     EventCompiler,
     TemplateCompiler,
     ModelCompiler
};
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BuildProject {

     protected Manifest $manifest;

     protected array $watch_dirs = [
           'routes',
           'models'
     ];

     protected function get_all_files(bool $changed = false){
         $files = [];

         //check saqle routes
         foreach ($this->watch_dirs as $dir){
             $this->scan_dir(path_join([config('saqle_routes_dirs')[0], $dir]), $files, $dir, $changed);
         }
    
         //check project folder
         foreach ($this->watch_dirs as $dir){
             $this->scan_dir(path_join([config('base_path'), $dir]), $files, $dir, $changed);
         }

         //check app folders
         foreach ($this->watch_dirs as $dir){
             foreach(config('app.modules') as $app){
                 $this->scan_dir( path_join([config('base_path'), 'modules', $app, $dir]), $files, $dir, $changed, $app);
             }
         }
         
         //remove deleted files from manifest
         foreach(array_keys($this->manifest->data) as $file){
             if(!file_exists($file)){
                 $files[] = ['path' => $file, 'type' => 'deleted', 'dir' => '', 'app' => ''];
                 $this->manifest->remove($file);
             }
         }
         
         return $files;
     }

     protected function scan_dir(string $path, array &$files, string $dir, $changed, ?string $app = null): void {
            if(!is_dir($path))
                return;
            
           $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
           );

           foreach($iterator as $file){
                if(!$file->isFile()) continue;

                $real_path = str_replace('\\', '/', $file->getRealPath());

                //skip vendor and storage directories
                if(stripos($real_path, '/vendor/') !== false || stripos($real_path, '/storage/') !== false){
                     continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                $mtime = $file->getMTime();
                $hash  = md5_file($path);

                if(!$changed){
                     $files[] = ['path' => $path, 'type' => 'modified', 'dir' => $dir, 'app' => $app];
                }else{
                    $old = $this->manifest->get($path);

                     if(!$old || $old['mtime'] !== $mtime || $old['hash'] !== $hash) {
                          $files[] = ['path' => $path, 'type' => 'modified', 'dir' => $dir, 'app' => $app];
                     }
                }

                $this->manifest->set($path, ['mtime' => $mtime, 'hash'  => $hash]);
           }
     }

     public function execute(string $type = 'all'){
         $this->manifest = new Manifest();

         switch($type){
             case "all":
                 echo "Building everything!";

                 //compile components
                 ComponentCompiler::compile();

                 //compile templates
                 TemplateCompiler::compile();

                 //compile models
                 ModelCompiler::compile();

                 //get modified files
                 $modified_files = $this->get_all_files();

                 //compile routes
                 RouteCompiler::compile($modified_files);
                 
                 //compile events
                 EventCompiler::compile();

                 //save the updated build manifest
                 $this->manifest->save();

                 echo "Build complete. Changed files: ".count($modified_files).PHP_EOL;
             break;
             case "resources":
                echo "Building resources!";
            break;
         }
     }
}
