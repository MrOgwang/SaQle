<?php

namespace SaQle\Orm\Entities\Model\Manager\Loaders;

class RelationStack {
     private array $stack = [];
     private static ?self $current = null;
     private static int $depth = 0;

     public static function enter_root(): self {
         if(!self::$current){
             self::$current = new self();
         }

         self::$depth++;

         return self::$current;
     }

     public static function leave_root(): void {
         self::$depth--;

         if(self::$depth === 0) {
             self::$current = null;
         }
     }

     public static function current(): ?self {
         return self::$current;
     }

     // ----- relation path API -----
     public function enter(string $relation): void {
         $this->stack[] = $relation;
     }

     public function leave(): void {
         array_pop($this->stack);
     }

     public function parent(): ?string {
         $count = count($this->stack);
         return $count > 1 ? $this->stack[$count - 2] : null;
     }

     public function full_path(): string {
         return implode('.', $this->stack);
     }
}
