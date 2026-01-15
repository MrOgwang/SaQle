<?php
namespace SaQle\Core\Registries;

final class CachedEventRegistry extends EventRegistry {
     private string $cache_path;

     public function __construct(
         private string $document_root,
         private string $class_mappings_dir
     ){
         $this->cache_path = $this->document_root.$this->class_mappings_dir."events.php";
         $this->load_from_cache();
     }

     public function save_to_cache(): void {
         $data = "<?php\nreturn " . var_export($this->get_all_listeners(), true) . ";\n";
         file_put_contents($this->cache_path, $data);
     }

     private function load_from_cache(): void {
         if (file_exists($this->cache_path)) {
             $cached_listeners = require $this->cache_path;
             foreach ($cached_listeners as $event => $listeners) {
                 $this->add($event, $listeners);
             }
         }
     }
}