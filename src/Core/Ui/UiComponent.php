<?php

namespace SaQle\Core\Ui;

class UiComponent {

     public const TARGET = '__TARGET__';

     /**
      * Component placeholder name in template
      * 
      * <ui:component/form name='whatever'/>
      * */
     protected string $name;

     /**
      * The name of the component itself
      * */
     protected string $component;

     protected UiComponent|string|null $slot = null;

     protected bool $target = false;

     protected bool $lazy = false;

     protected bool $cache = false;

     //@var array<string, UiComponent>
     protected array $blocks = [];

     public function __construct(string $name, ?string $component = null){
         $this->name = $name;
         $this->component = $component ?? $name;
     }

     public static function make(string $name, ?callable $callback = null) : static {
         $component = new UiComponent($name);

         if($callback){
             $callback($component);
         }

         return $component;
     }

     public function get_name() : string {
         return $this->name;
     }

     public function get_component() : string {
         return $this->component;
     }

     public function target() : bool {
         $this->target = true;
         return $this;
     }

     public function expects_target() : bool {
         return $this->target;
     }

     public function slot(UiComponent|string|null $slot = null) : static {

         if($slot === Ui::TARGET){
             $this->expects_target = true;
             return $this;
         }

         $this->slot = $slot;

         return $this;
     }

     public function get_slot() : UiComponent|string|null {
         return $this->slot;
     }

     public function block(string $name, string $component) : static {

         $block = new UiComponent($name, $component);

         $this->blocks[$name] = $block;

         return $this;
     }

     public function get_blocks() : array {
         return $this->blocks;
     }

     public function get_block(string $name) : UiComponent|null {
         return $this->blocks[$name] ?? null;
     }

     public function lazy() : static {
         $this->lazy = true;
         return $this;
     }

     public function cache() : static {
         $this->cache = true;
         return $this;
     }

     public function is_lazy() : bool {
         return $this->lazy;
     }

     public function is_cacheable() : bool {
         return $this->cache;
     }
}