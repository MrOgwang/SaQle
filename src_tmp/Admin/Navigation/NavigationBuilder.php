<?php

namespace SaQle\Admin\Navigation;

class NavigationBuilder {

     public GroupCollection $groups;

     public LinkCollection $links;

     private ?NavigationLink $_primary_action = null;

     public function __construct() {
         $this->groups = new GroupCollection();
         $this->links  = new LinkCollection();
     }

     public function primary_action(string $name, string $label, string $route, string $icon = ""){

         $this->_primary_action = new NavigationLink($name, $label, $route, $icon);

     }

     public function get_primary_action(){
         return $this->_primary_action;
     }

     public function tree(): array {

         $groups = $this->groups->ordered();

         $links = $this->links->grouped();

         foreach($groups as $group){
             $group->links($links[$group->name] ?? []);
         }

         return $groups;
     }
     
}