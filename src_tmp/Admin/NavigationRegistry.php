<?php

namespace SaQle\Admin;

class NavigationRegistry {
     /**
     * @var NavigationGroup[]
     */
     protected array $groups = [];

     protected ?NavigationLink $action = null;

     public function group(string $label, callable $callback) : self {

         $key = mb_strtolower(trim($label));

         $group = $this->groups[$key] ?? new NavigationGroup($label);

         $callback($group);

         $this->groups[$key] = $group;

         return $this;

     }

     public function primary_action(string $label, string $url, ?string $icon = null) : self {
         $this->action = new NavigationLink(
             label: $label,
             url: $url,
             icon: $icon,
             show: true,
             active: true
         );

         return $this;
     }

     /**
     * @return NavigationGroup[]
     */
     public function groups(): array {
         return array_values($this->groups);
     }

     public function get_primary_action() : ?NavigationLink {
         return $this->action;
     }

     public function order(array $groups) : self {

         if(empty($groups)){
             return $this;
         }

         $ordered_groups = [];

         foreach($groups as $g){

             $key = mb_strtolower(trim($g));

             if(isset($this->groups[$key])){
                 $ordered_groups[$key] = $this->groups[$key];
             }

         }

         $this->groups = $ordered_groups;

         return $this;

     }
}