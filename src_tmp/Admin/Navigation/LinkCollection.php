<?php

namespace SaQle\Admin\Navigation;

class LinkCollection {

     protected array $links = [];

     protected array $ordering = [];

     public function add(...$args): NavigationLink {

         $link = new NavigationLink(...$args);

         $this->links[$link->name] = $link;

         return $link;
     }

     public function get(string $name): ?NavigationLink {
         return $this->links[$name] ?? null;
     }

     public function edit(string $name, callable $callback): void {
         if($link = $this->get($name)){
             $callback($link);
         }
     }

     public function hide(array|string $name): void {

         $names = is_array($name) ? $name : [$name];

         foreach($names as $n){
             $this->edit($n, fn($l) => $l->hidden());
         }
     }

     public function show(string $name): void {
         $this->edit($name, fn($l) => $l->visible());
     }

     public function remove(string $name): void {
         unset($this->links[$name]);
     }

     public function move(string $name, string $group): void {
         $this->edit($name, fn($l) => $l->group($group));
     }

     public function order(string $group, array $links): void {
         $this->ordering[$group] = $links;
     }

     public function before(string $anchor, string $moving): void {
         // optional helper
     }

     public function after(string $anchor, string $moving): void {
         // optional helper
     }

     public function all(): array {
         return $this->links;
     }

     public function ordering(): array {
         return $this->ordering;
     }

     public function grouped(): array {

         $groups = [];

         foreach($this->links as $link){

             if(!$link->is_visible()){
                 continue;
             }

             $groups[$link->group][] = $link;

         }

         foreach($groups as $group => &$links){

             usort($links, fn($a, $b) => $a->weight <=> $b->weight);

             if(isset($this->ordering[$group])){

                 $links = $this->apply_ordering(
                     $links,
                     $this->ordering[$group]
                 );

             }

         }

         return $groups;
     }
}