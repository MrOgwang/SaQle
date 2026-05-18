<?php
namespace SaQle\Auth\Guards;

/**
 * Evaluate guards provided in the form of 
 * Abstract Syntax Tree (AST)
 * */

use RuntimeException;

final class GuardEvaluator {

     public static function authorize(array $ast) : GuardResult {
         if(!$ast){
             return new GuardResult(true);
         }
         
         return self::evaluate_node($ast);
     }

     private static function evaluate_node(array $node) : GuardResult {
         if(!isset($node['type'])){
             //throw new RuntimeException('Invalid AST node.');
         }

         return match ($node['type']) {
             'guard' => self::evaluate_guard($node),
             'and' => self::evaluate_and($node),
             'or' => self::evaluate_or($node),
             'not' => self::evaluate_not($node),
             default => throw new RuntimeException("Unknown node type '{$node['type']}'")
         };
     }

     //guard node
     private static function evaluate_guard(array $node) : GuardResult {
         $guard = $node['value'];

         if(Guard::check($guard)){
             return new GuardResult(true);
         }

         return new GuardResult(
             false,
             $guard,
             fn() => Guard::fail($guard),
             [$guard]
         );
     }

     //and node
     private static function evaluate_and(array $node) : GuardResult {
         $left = self::evaluate_node($node['left']);

         if(!$left->passed){
             return $left;
         }

         $right = self::evaluate_node($node['right']);

         if(!$right->passed){
             return $right;
         }

         return new GuardResult(true);
     }

     /**
     * Or node
     *
     * First pass wins.
     * If all fail:
     * - collect failures
     * - choose ONE failure handler
     */
     private static function evaluate_or(array $node) : GuardResult {
         $left = self::evaluate_node($node['left']);

         if($left->passed){
             return $left;
         }

         $right = self::evaluate_node($node['right']);

         if($right->passed){
             return $right;
         }

         $failed_guards = array_merge($left->failed_guards, $right->failed_guards);

         return new GuardResult(
             false,
             $left->failed_guard,
             fn() => self::handle_or_failure($left, $right),
             $failed_guards
         );
     }

     //not node
     private static function evaluate_not(array $node) : GuardResult {
         $child = self::evaluate_node($node['child']);

         //invert results
         if(!$child->passed) {
             return new GuardResult(true);
         }

         return new GuardResult(
             false,
             $child->failed_guard,
             $child->on_fail,
             $child->failed_guards
         );
     }

     //or failure strategy
     private static function handle_or_failure(GuardResult $left, GuardResult $right) {
         /**
         * Current strategy:
         * Use first available failure handler.
         */
         if($left->on_fail){
             return($left->on_fail)();
         }

         if($right->on_fail){
             return ($right->on_fail)();
         }

         return null;
     }
}