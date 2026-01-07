<?php

namespace SaQle\Auth\Guards;

use RuntimeException;

final class AuthorizationEvaluator {
     public static function authorize(array $guard_groups): bool {
         foreach ($guard_groups as $group) {
             if (!self::evaluate_group($group)) {
                 return false;
             }
         }

         return true;
     }

     private static function evaluate_group(array $group): bool{
         if (!isset($group['mode'], $group['guards'])){
             throw new RuntimeException('Invalid guard group structure.');
         }

         $mode   = $group['mode'];
         $guards = $group['guards'];

         if ($mode === 'all') {
             return self::check_all($guards);
         }

         if ($mode === 'any') {
             return self::check_any($guards);
         }

         throw new RuntimeException("Unknown guard mode '{$mode}'.");
     }

     private static function check_all(array $guards): bool {
         foreach ($guards as $guard) {
             if (!Guard::check($guard)) {
                 return false;
             }
         }

         return true;
     }

     private static function check_any(array $guards): bool {
         foreach ($guards as $guard) {
             if (Guard::check($guard)) {
                 return true;
             }
         }

         return false;
     }
}
