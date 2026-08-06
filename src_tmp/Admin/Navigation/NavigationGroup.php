<?php

namespace SaQle\Admin\Navigation;

class NavigationGroup {

     private array $_links = [];

     public function __construct(
         public string $name,
         public string $label,
         public string $icon = '',
         public bool   $visible = true,
         public int    $weight = -1
     ) {}

     public function label(string $label): static {

         $this->label = $label;

         return $this;
     }

     public function icon(string $icon): static {

         $this->icon = $icon;

         return $this;
     }

     public function hidden(): static {

         $this->visible = false;

         return $this;
     }

     public function visible(): static {

         $this->visible = true;

         return $this;
     }

     public function weight(int $weight) : static {
        
         $this->weight = $weight;

         return $this;
     }

     public function is_visible() : bool {

         return $this->visible;

     }

     public function links(?array $links = null) : array {
         if($links){
             $this->_links = $links;
         }

         return $this->_links;
     }
}