<?php

namespace SaQle\Admin\Resources;

class ResourceRegistry {
     /**
     * @var ResourceDefinition[]
     */
     protected array $resources = [];

     public function add(ResourceDefinition $resource): static {

         $this->resources[$resource->name()] = $resource;

         return $this;
     }

     public function get(string $name): ?ResourceDefinition {
         return $this->resources[$name] ?? null;
     }

     public function edit(string $name, callable $callback): static {
         if($resource = $this->get($name)){
             $callback($resource);
         }

         return $this;
     }

     public function has(string $name): bool {
         return isset($this->resources[$name]);
     }

     /**
     * @return ResourceDefinition[]
     */
     public function all() : array {
         return $this->resources;
     }
}