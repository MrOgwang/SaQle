<?php

namespace SaQle\Core\Support;

class ActorContext {
     private static mixed $actor = null;

     public static function set(mixed $actor) : void {
         self::$actor = $actor;
     }

     public static function get() : mixed {
         return self::$actor;
     }

     public static function id() : mixed {
         return self::$actor?->user_id ?? null;
     }

     public static function is_system() : bool {
         return self::$actor?->first_name === 'System' || self::$actor === 'System';
     }
}