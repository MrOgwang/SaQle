<?php

namespace SaQle\Auth\Guards;

use RuntimeException;

final class AuthorizationEvaluator {

     public static function authorize(array $guard_groups) : AuthorizationResult {
         foreach($guard_groups as $group){
             $result = self::evaluate_group($group);
             if(!$result->passed){
                 return $result; // fail fast
             }
         }

         return new AuthorizationResult(true);
     }

     private static function evaluate_group(array $group) : AuthorizationResult {
         if(!isset($group['mode'], $group['guards'])){
             throw new RuntimeException('Invalid guard group structure.');
         }

         return match ($group['mode']){
             'all' => self::check_all($group['guards']),
             'any' => self::check_any($group['guards']),
             default => throw new RuntimeException("Unknown guard mode '{$group['mode']}'"),
         };
     }

     //Fail fast and return THAT guard's handler
     private static function check_all(array $guards) : AuthorizationResult {
         foreach($guards as $guard){
             if(!Guard::check($guard)){
                 return new AuthorizationResult(
                     false,
                     $guard,
                     fn() => Guard::fail($guard)
                 );
             }
         }

         return new AuthorizationResult(true);
     }

     //Handle "any"
     private static function check_any(array $guards) : AuthorizationResult {
         $failed = [];

         foreach($guards as $guard){
             if(Guard::check($guard)){
                 return new AuthorizationResult(true);
             }

             $failed[] = $guard;
         }

         //ALL failed → decide how to respond
         return new AuthorizationResult(
             false,
             null,
             fn() => self::handle_any_failure($failed),
             $failed
         );
     }

     private static function handle_any_failure(array $guards){
         /**
         * Strategy (very important design choice):
         * ---------------------------------------
         * You CANNOT safely execute multiple fail handlers (they may redirect/throw).
         *
         * So pick ONE:
         *
         * Option A (recommended): First guard's handler
         * Option B: Priority system (future)
         * Option C: Custom "any" handler (advanced)
         */

         $first = $guards[0] ?? null;

         if($first){
             return Guard::fail($first);
         }

         return null;
     }
}
