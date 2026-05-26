<?php

namespace SaQle\Core\Ui\Utils;

use RuntimeException;
use Throwable;

class ExpressionEvaluator {

     public static function evaluate(string $expression, array $scope = []) : mixed {
        
         extract($scope);

         try{
             return eval('return '.$expression.';');
         }catch(Throwable $e){
             throw new RuntimeException("Expression evaluation failed: {$expression}");
         }
     }

}