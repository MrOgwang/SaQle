<?php

namespace SaQle\Commons;

final class Arr {

     function equal(array $array1, array $array2): bool {

         if(count($array1) !== count($array2)) {
             return false;
         }

         sort($array1);
         sort($array2);

         return $array1 === $array2;
     }

}