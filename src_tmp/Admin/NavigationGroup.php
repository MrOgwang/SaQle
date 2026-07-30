<?php

namespace SaQle\Admin;

class NavigationGroup {

     protected bool  $show = true;

     protected string $label;

     protected array $links = [];

     public function __construct(string $label, bool $show = true){
         $this->label = $label;
         $this->show  = $show;
     }

     public function link(string $label, string $url, ?string $icon = null, bool $show = true, bool $active = false): self {
         
         $key = mb_strtolower(trim($label));

         $this->links[$key] = new NavigationLink(
             label: $label,
             url: $url,
             icon: $icon,
             show: $show,
             active: $active
         );

         return $this;
     }

     public function label(): string {
         return $this->label;
     }

     /**
     * @return NavigationLink[]
     */
     public function links(): array {
         return array_values($this->links);
     }

     public function should_show(){
         return $this->show;
     }
}