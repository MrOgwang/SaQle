<?php

namespace SaQle\Core\Support;

final class FrameworkEnvironment {

     public static function framework_path(): string {
         return realpath(dirname(__DIR__, 3));
     }

     public static function is_vendor(): bool {

         $framework_path = self::framework_path();

         $vendor_path = realpath(config('base_path').DIRECTORY_SEPARATOR.'vendor');

         return $vendor_path !== false && self::is_inside($framework_path, $vendor_path);
     }

     public static function is_inside_project(): bool {
         return self::is_inside(self::framework_path(), config('base_path'));
     }

     public static function is_external(): bool {
         return !self::is_inside_project();
     }

     private static function is_inside(string $path, string $parent) : bool {

         $path = rtrim($path, DIRECTORY_SEPARATOR);

         $parent = rtrim($parent, DIRECTORY_SEPARATOR);

         return $path === $parent || str_starts_with($path, $parent.DIRECTORY_SEPARATOR);
     }
}