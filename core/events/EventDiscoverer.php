<?php
namespace SaQle\Core\Events;

use ReflectionClass;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SaQle\Core\Support\Listens;
use SaQle\Core\Registries\EventRegistry;

final class EventDiscoverer {
     public function __construct(
         private array $directories = []
     ){}

     public function discover_and_register(EventRegistry $registry): void {
         foreach ($this->get_listener_classes() as $listener_class){
             $reflection = new ReflectionClass($listener_class);
             $attributes = $reflection->getAttributes(Listens::class);
             if (!$attributes) {
                 continue;
             }
             $instance = $attributes[0]->newInstance();

             $events = is_array($instance->events) ? $instance->events : [$instance->events];

             foreach ($events as $event) {
                 $registry->add($event, [$listener_class]);
             }
         }
     }

     private function get_listener_classes(): array {
         $classes = [];
         foreach ($this->directories as $dir) {
             if(is_dir($dir)){
                 $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
                 foreach ($files as $file) {
                     if ($file->isFile() && $file->getExtension() === 'php') {
                         $class_name = $this->get_class_from_file($file->getPathname());
                         if ($class_name) {
                             $classes[] = $class_name;
                         }
                     }
                 }
             }
         }
         return $classes;
     }

     private function get_class_from_file(string $file_path): ?string {
         $contents = file_get_contents($file_path);
         if (preg_match('/namespace\s+(.+?);.*class\s+(.+?)\s*(extends|implements|{)/s', $contents, $matches)) {
             return $matches[1] . '\\' . $matches[2];
         }
         return null;
     }
}