<?php

namespace SaQle\Admin\Navigation;

class NavigationLink {

     protected mixed $badge = null;

     protected mixed $visible_resolver = null;

     protected mixed $permission = null;

     public function __construct(
         public string $name,
         public string $label,
         public string $route,
         public string $icon,
         public string $group = "",
         public bool   $visible = true,
         public int    $weight = -1
     ){}

     public function label(string $label): static {

         $this->label = $label;

         return $this;
     }

     public function route(string $route): static {

         $this->route = $route;

         return $this;
     }

     public function icon(string $icon): static {

         $this->icon = $icon;

         return $this;
     }

     public function group(string $group): static {

         $this->group = $group;

         return $this;
     }

     public function weight(int $weight) : static {

         $this->weight = $weight;

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

     public function is_visible(): bool {

         return $this->visible;

     }

     public function badge(callable|int|string $badge): static {

         $this->badge = $badge;

         return $this;
     }

     public function can(string|callable $permission): static {

         $this->permission = $permission;

         return $this;
     }

     public function visible_when(callable $callback): static {

         $this->visible_resolver = $callback;

         return $this;
     }
}