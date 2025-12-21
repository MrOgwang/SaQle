<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\{Manifest, ClassMapper, TargetCompiler};
use SaQle\Routes\Router;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

class BuildProject{

      protected Manifest $manifest;

      protected ClassMapper $classmapper;

      protected TargetCompiler $targetcompiler;

      protected array $watch_dirs = [
           'routes',
           'models'
      ];

      protected function get_all_files(string $project_root, bool $changed = false){
           $files = [];
    
           //check project folder
           foreach ($this->watch_dirs as $dir){
                $this->scan_dir($project_root.DIRECTORY_SEPARATOR.$dir, $files, $dir, $changed);
           }

           //check app folders
           foreach ($this->watch_dirs as $dir){

                foreach(INSTALLED_APPS as $app){
                     $this->scan_dir($project_root.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR.$dir, $files, $dir, $changed, $app);
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

      protected function scan_dir(string $path, array &$files, string $dir, $changed, ?string $app = null): void{
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

      public function execute(string $project_root, string $type = 'all'){
           $this->manifest = new Manifest($project_root);
           switch($type){
                case "all":
                     echo "Building everything!";

                     //map controller classes and view names
                     $this->classmapper = new ClassMapper($project_root);
                     $this->classmapper->map();

                     //get modified files
                     $files = $this->get_all_files($project_root);

                     //compile project routes and layoutes
                     $this->targetcompiler = new TargetCompiler($project_root);
                     $this->targetcompiler->compile($files);

                     //save the updated build manifest
                     $this->manifest->save();

                     echo "Build complete. Changed files: ".count($files).PHP_EOL;
                break;
                case "resources":
                    echo "Building resources!";
                break;
                case "views":
                    echo "Building views!";
                break;
                case "block":
                     //$this->switch_to_components($project_root);
                     //$this->adjust_namespaces2($project_root);
                     echo "Project root: $project_root\n";
                     $this->remove_duplicate_use($project_root);
                break;
           }
      }

      protected function switch_to_components($project_root){
           $paths = [$project_root];

           //app folders
           foreach(INSTALLED_APPS as $app){
                $paths[] = $project_root.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.$app.DIRECTORY_SEPARATOR;
           }

           foreach($paths as $basePath){
                $controllersPath = $basePath . '/controllers';
                $templatesPath = $basePath . '/templates';
                $componentsPath = $basePath . '/components';

                if (!is_dir($componentsPath)) {
                     mkdir($componentsPath, 0777, true);
                }

                $controllers = [];
                $templates = [];

                //Collect controllers
                foreach (glob($controllersPath . '/*.php') as $file) {
                     $name = pathinfo($file, PATHINFO_FILENAME);
                     $controllers[$name] = $file;
                }

                //Collect templates
                foreach (glob($templatesPath . '/*.html') as $file) {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $templates[$name] = $file;
                }

                //Build a unique list of all names
                $allNames = array_unique(array_merge(array_keys($controllers), array_keys($templates)));

                foreach ($allNames as $name){
                     $componentFolder = $componentsPath.'/'.$name;

                     if(!is_dir($componentFolder)){
                          mkdir($componentFolder, 0777, true);
                     }

                     //Move controller if exists
                     if (isset($controllers[$name])){
                          rename($controllers[$name], $componentFolder.'/'.$name.'.php');
                     }

                     //Move template if exists
                     if (isset($templates[$name])) {
                          rename($templates[$name], $componentFolder.'/'.$name.'.html');
                     }
                }
           }
      }

      protected function adjust_namespaces($projectRoot){
           $baseNamespace = 'Booibo';
           $projectRoot = str_replace('\\', '/', $projectRoot);

           $paths = [$projectRoot];

           //app folders
           foreach(INSTALLED_APPS as $app){
                $paths[] = $projectRoot.'/apps/'.$app.'/';
           }

           foreach($paths as $basePath){
                $componentsDir = $basePath."/components";
                $iterator = new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($componentsDir, FilesystemIterator::SKIP_DOTS)
                );

                foreach($iterator as $file){
                     if($file->isFile() && $file->getExtension() === 'php'){
                          $path = $file->getPathname();
                          $path = str_replace('\\', '/', $path);

                          // Compute relative path from project root
                          $relative = str_replace($projectRoot.'/', '', $path);

                          echo "Path: $path\n";
                          echo "Relative: $relative\n";

                          // Extract folder parts from the relative path
                          $parts = explode('/', dirname($relative));

                          // Convert folder names (which are lowercase) → PascalCase
                          $parts = array_map(function ($p) {
                                return ucfirst(strtolower($p));
                          }, $parts);

                          $parts = array_filter($parts, function ($p) {
                               return trim($p) !== '';
                          });

                          $parts = array_values($parts);

                          print_r($parts);

                          // Build final namespace
                          $namespace = $baseNamespace . '\\' . implode('\\', $parts);

                          // Load file
                          $contents = file_get_contents($path);

                          // Replace namespace line
                          $contents = preg_replace(
                                '/^namespace\s+[^;]+;/m',
                                'namespace ' . $namespace . ';',
                                $contents
                          );

                          file_put_contents($path, $contents);

                          echo "$namespace: Updated namespace in: $path\n";
                          echo "\n----------------------------\n";
                     }
                }
           }
      }

      protected function adjust_namespaces2($projectRoot){
           $projectRoot = str_replace('\\', '/', $projectRoot);

           $map = []; // old full class ref => new full class ref

           // =============================================
           // STEP 1 — Scan all component controllers
           // =============================================
           $componentDirs = [
              $projectRoot . '/components',        // top-level components
              $projectRoot . '/apps'               // apps/components inside apps
           ];

           //Top-level components
           $componentDirs = [$projectRoot . '/components'];

           // Apps/components
           $appRoot = $projectRoot . '/apps';
           if (is_dir($appRoot)) {
                foreach (scandir($appRoot) as $appName) {
                     if ($appName === '.' || $appName === '..') continue;
                     $appDir = $appRoot . '/' . $appName;
                     if (!is_dir($appDir)) continue;

                     $appComponents = $appDir . '/components';
                     if (is_dir($appComponents)) {
                          $componentDirs[] = $appComponents;
                     }
                }
           }


           foreach ($componentDirs as $baseDir){
                if (!is_dir($baseDir)) continue;

                $iterator = new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                     if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') continue;

                     $path = str_replace('\\', '/', $file->getRealPath());
                     $contents = file_get_contents($path);

                     // Extract the namespace declared inside the file
                     if (!preg_match('/namespace\s+([^;]+);/i', $contents, $nsMatch)) continue;
                     $namespace = trim($nsMatch[1]);

                     // Extract the class name
                     if (!preg_match('/class\s+([A-Za-z0-9_]+)/i', $contents, $classMatch)) continue;
                     $className = trim($classMatch[1]);

                     $fullNew = $namespace . '\\' . $className;

                     // Build old full reference (for mapping)
                     // OLD structure: replace \Components\ -> \Controllers\
                     //$fullOld = preg_replace('/\\\\Components\\\\/i', '\\Controllers\\', $fullNew);
                     //$fullOld = preg_replace('/\\\\Components\\\\[^\\\\]+$/i', '\\components\\' . $className, $fullNew);

                     $namespaceParts = explode('\\', $fullNew);
                     $componentIndex = array_search('Components', $namespaceParts);
                     $oldParts = $namespaceParts;
                     $oldParts[$componentIndex] = 'Components';  // Replace Components -> Controllers
                     array_splice($oldParts, $componentIndex + 1, 2);     // Remove the component folder
                     $fullOld = implode('\\', $oldParts) . '\\' . $className;


                     $map[$fullOld] = $fullNew;
                }
           }

          // =============================================
          // STEP 2 — Scan all PHP files and update use statements
          // =============================================
          $iterator = new RecursiveIteratorIterator(
              new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS)
          );

          foreach ($iterator as $file) {
              if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') continue;

              $path = str_replace('\\', '/', $file->getRealPath());
              $contents = file_get_contents($path);
              $updated = $contents;

              // ---------------------------
              // 1. Handle single imports
              // ---------------------------
              foreach ($map as $oldFull => $newFull) {
                  $pattern = '/use\s+' . preg_quote($oldFull, '/') . '\s*;/i';
                  $replacement = 'use ' . $newFull . ';';
                  $updated = preg_replace($pattern, $replacement, $updated);
              }

              // ---------------------------
              // 2. Handle grouped imports
              // Example: use Apps\Talent\Controllers\{ExpertProfile, AnotherController};
              // ---------------------------
              $updated = preg_replace_callback(
                  '/use\s+([^\{]+)\\\\\{([^\}]+)\};/i',
                  function ($matches) use ($map) {
                      $prefix = trim($matches[1]);        // e.g., Apps\Talent\Controllers
                      $classes = array_map('trim', explode(',', $matches[2]));

                      $lines = [];
                      foreach ($classes as $className) {
                          $oldFull = $prefix . '\\' . $className;
                          if (isset($map[$oldFull])) {
                              $lines[] = 'use ' . $map[$oldFull] . ';';
                          } else {
                              // fallback: keep original
                              $lines[] = 'use ' . $oldFull . ';';
                          }
                      }
                      return implode("\n", $lines);
                  },
                  $updated
              );

              // Save if changed
              if ($updated !== $contents) {
                  file_put_contents($path, $updated);
                  echo "Updated use statements in: $path\n";
              }
          }


          echo "DONE updating all use statements for component controllers.\n";
      }

      protected function remove_duplicate_use($projectRoot){
           $projectRoot = str_replace('\\', '/', $projectRoot);
           $iterator = new RecursiveIteratorIterator(
              new RecursiveDirectoryIterator($projectRoot, FilesystemIterator::SKIP_DOTS)
           );

           foreach ($iterator as $file) {
              if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
                  continue;
              }

              $path = str_replace('\\', '/', $file->getRealPath());

              // Skip vendor directory
                if (stripos($path, '/vendor/') !== false){
                     continue;
                }

              $contents = file_get_contents($path);

              // Break file into lines for safe manipulation
              $lines = preg_split("/\r\n|\n|\r/", $contents);

              $phpIndex         = null;
              $declareIndex     = null;
              $namespaceIndex   = null;
              $firstClassIndex  = null;

              // -------------------------------------------
              // 1. Detect structure
              // -------------------------------------------
              foreach ($lines as $i => $line) {
                  $trim = trim($line);

                  // Opening tag
                  if ($phpIndex === null && $trim === '<?php') {
                      $phpIndex = $i;
                      continue;
                  }

                  // declare(strict_types=1)
                  if ($declareIndex === null &&
                      preg_match('/^declare\s*\(\s*strict_types\s*=\s*\d+\s*\)\s*;$/i', $trim)) {
                      $declareIndex = $i;
                      continue;
                  }

                  // namespace
                  if ($namespaceIndex === null &&
                      preg_match('/^namespace\s+[^;]+;/i', $trim)) {
                      $namespaceIndex = $i;
                      continue;
                  }

                  // class, interface, trait, enum — marks end of "header" region
                  if ($firstClassIndex === null &&
                      preg_match('/^(final\s+|abstract\s+)?(class|interface|trait|enum)\s+/i', $trim)) {
                      $firstClassIndex = $i;
                      continue;
                  }
              }

              // If no class/interface/trait/enum, skip safely
              if ($phpIndex === null || $firstClassIndex === null) {
                  continue;
              }

              // -------------------------------------------
              // 2. Collect only use statements BEFORE class
              // -------------------------------------------
              $useLines = [];
              $useIndexes = [];

              for ($i = $phpIndex + 1; $i < $firstClassIndex; $i++) {
                  $trim = trim($lines[$i]);

                  if (preg_match('/^use\s+[^;]+;/i', $trim)) {
                      $useLines[] = $trim;
                      $useIndexes[] = $i;
                  }
              }

              if (empty($useLines)) {
                  continue; // nothing to fix
              }

              // -------------------------------------------
              // 3. Deduplicate use statements
              // -------------------------------------------
              $uniqueUseLines = array_values(array_unique($useLines));

              // -------------------------------------------
              // 4. Reconstruct the file header correctly
              // -------------------------------------------
              $newHeader = [];
              $newHeader[] = '<?php';

              if ($declareIndex !== null) {
                  $newHeader[] = trim($lines[$declareIndex]);
              }

              if ($namespaceIndex !== null) {
                  $newHeader[] = trim($lines[$namespaceIndex]);
              }

              // Append unique use statements
              foreach ($uniqueUseLines as $useLine) {
                  $newHeader[] = $useLine;
              }

              // -------------------------------------------
              // 5. Build new final file contents
              // -------------------------------------------
              $rebuilt = [];

              // Add header
              foreach ($newHeader as $l) {
                  $rebuilt[] = $l;
              }

              // Add rest of the file, skipping old header lines
              for ($i = $phpIndex + 1; $i < $firstClassIndex; $i++) {
                  if ($i === $declareIndex) continue;
                  if ($i === $namespaceIndex) continue;
                  if (in_array($i, $useIndexes)) continue;
              }

              // Add everything after the class start untouched
              for ($i = $firstClassIndex; $i < count($lines); $i++) {
                  $rebuilt[] = $lines[$i];
              }

              $newContents = implode("\n", $rebuilt);

              if ($newContents !== $contents) {
                  file_put_contents($path, $newContents);
                  echo "Fixed duplicate use statements in: $path\n";
              }
           }

          echo "Done.\n";
      }
}
