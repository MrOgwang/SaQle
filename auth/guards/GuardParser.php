<?php

namespace SaQle\Auth\Guards;

/**
 * Parses a guard expression into a Abstract Syntax Tree (AST)
 * 
 * Supported:
 *
 *  &&   AND
 *  ||   OR
 *  !    NOT
 *  ()   GROUPING
 *
 * Examples:
 *
 *  authenticated
 *
 *  authenticated && admin
 *
 *  authenticated && (admin || manager)
 *
 *  authenticated && !banned
 *
 *  (admin || moderator) && verified
 *
 */

use Exception;

class GuardParser {

     private array $tokens = [];

     private int $position = 0;

     public function parse(string $expression) : array {
         $this->tokens = $this->tokenize($expression);
         $this->position = 0;

         $ast = $this->parse_or();

         if($this->current() !== null){
             throw new Exception('Unexpected token: '.$this->current());
         }

         return $ast;
     }

     private function tokenize(string $expression) : array {
         $pattern = '/
            (\&\&)         # &&
            |(\|\|)        # ||
            |(!)           # !
            |(\()          # (
            |(\))          # )
            |([a-zA-Z_][a-zA-Z0-9_]*) # identifiers
         /x';

         preg_match_all($pattern, $expression, $matches);

         return array_values(
             array_filter(
                 $matches[0],
                 fn($token) => trim($token) !== ''
             )
         );
     }

     private function parse_or() : array {
         $left = $this->parse_and();

         while ($this->match('||')) {
             $right = $this->parse_and();

             $left = [
                 'type' => 'or',
                 'left' => $left,
                 'right' => $right,
             ];
         }

         return $left;
     }

     private function parse_and() : array {
         $left = $this->parse_not();

         while($this->match('&&')){
             $right = $this->parse_not();

             $left = [
                 'type' => 'and',
                 'left' => $left,
                 'right' => $right,
             ];
         }

         return $left;
     }

     private function parse_not() : array {
         if($this->match('!')) {
             return [
                 'type' => 'not',
                 'child' => $this->parse_not(),
             ];
         }

         return $this->parse_primary();
     }

     private function parse_primary() : array {
         if($this->match('(')){
             $expr = $this->parse_or();
             $this->consume(')');
             return $expr;
         }

         $token = $this->current();

         if($token === null){
             throw new Exception('Unexpected end of expression');
         }

         if(!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $token)) {
             throw new Exception("Invalid token: {$token}");
         }

         $this->advance();

         return [
             'type' => 'guard',
             'value' => $token,
         ];
     }

     //token helpers
     private function current() : ?string {
         return $this->tokens[$this->position] ?? null;
     }

     private function advance() : void {
         $this->position++;
     }

     private function match(string $token) : bool {
         if($this->current() === $token){
             $this->advance();
             return true;
         }

         return false;
     }

     private function consume(string $token) : void {
         if(!$this->match($token)){
             throw new Exception("Expected '{$token}', got '{$this->current()}'");
         }
     }
}
