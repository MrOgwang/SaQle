<?php

namespace SaQle\Admin\Navigation;

class GroupCollection {

     protected array $groups = [];

     protected array $order = [];

     public function add(...$args): NavigationGroup {

         $group = new NavigationGroup(...$args);

         $this->groups[$group->name] = $group;

         return $group;
     }

     public function get(string $name): ?NavigationGroup {
         return $this->groups[$name] ?? null;
     }

     public function edit(string $name, callable $callback): void {
         if($group = $this->get($name)){
             $callback($group);
         }
     }

     public function hide(string $name): void {
         $this->edit($name, fn($g) => $g->hidden());
     }

     public function show(string $name): void {
         $this->edit($name, fn($g) => $g->visible());
     }

     public function remove(string $name): void {
         unset($this->groups[$name]);
     }

     public function order(array $groups): void {
         $this->order = $groups;
     }

     public function all(): array {
         return $this->groups;
     }

     public function ordering(): array {
         return $this->order;
     }

     public function ordered(): array {

         $groups = $this->groups;

         uasort($groups, function ($a, $b){

             return $a->weight <=> $b->weight;

         });

         if($this->order){

             $ordered = [];

             foreach($this->order as $name){

                 if(isset($groups[$name])){

                     $ordered[$name] = $groups[$name];
                     unset($groups[$name]);

                 }

             }

             $groups = $ordered + $groups;

         }

         return array_values(
             array_filter($groups, fn($g) => $g->is_visible())
         );
     }
}