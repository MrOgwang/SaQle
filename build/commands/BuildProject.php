<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\{Manifest, ClassMapper, TargetCompiler};
use SaQle\Routes\Router;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BuildProject{

      protected Manifest $manifest;

      protected ClassMapper $classmapper;

      protected TargetCompiler $targetcompiler;

      protected array $watch_dirs = [
           'routes'
      ];

      protected function get_changed_files(string $project_root){
           $changed = [];
    
           //check project folder
           foreach ($this->watch_dirs as $dir){
                $this->scan_dir($project_root.DIRECTORY_SEPARATOR.$dir, $changed, $dir);
           }

           //check app folders
           foreach ($this->watch_dirs as $dir){

                foreach(INSTALLED_APPS as $app){
                     $this->scan_dir($project_root.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$dir, $changed, $dir, $app);
                }
           }

           //remove deleted files from manifest
           foreach(array_keys($this->manifest->data) as $file){
                if(!file_exists($file)){
                     $changed[] = ['path' => $file, 'type' => 'deleted', 'dir' => '', 'app' => ''];
                     $this->manifest->remove($file);
                }
           }

           return $changed;
      }

      protected function scan_dir(string $path, array &$changed, string $dir, ?string $app = null): void{
           $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
           );

           foreach($iterator as $file){
                if(!$file->isFile()) continue;

                $path = str_replace('\\', '/', $file->getPathname());
                $mtime = $file->getMTime();
                $hash  = md5_file($path);

                $old = $this->manifest->get($path);

                if (!$old || $old['mtime'] !== $mtime || $old['hash'] !== $hash) {
                     $changed[] = ['path' => $path, 'type' => 'modified', 'dir' => $dir, 'app' => $app];
                }

                $this->manifest->set($path, ['mtime' => $mtime, 'hash'  => $hash]);
           }
      }

      public function execute(string $project_root, string $type = 'all'){
           $this->manifest = new Manifest($project_root);
           switch($type){
                case "all":
                     echo "Building everything!";

                     //map controller classes and view names
                     $this->classmapper = new ClassMapper($project_root);
                     $this->classmapper->map();

                     //get modified files
                     $changed = $this->get_changed_files($project_root);

                     //compile project routes and layoutes
                     $this->targetcompiler = new TargetCompiler($project_root);
                     $this->targetcompiler->compile($changed);

                     //save the updated build manifest
                     $this->manifest->save();

                     echo "Build complete. Changed files: ".count($changed).PHP_EOL;
                break;
                case "resources":
                    echo "Building resources!";
                break;
                case "views":
                    echo "Building views!";
                break;
           }
      }
}
