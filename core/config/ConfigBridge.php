<?php

namespace SaQle\Core\Config;

final class ConfigBridge {
     public static function expose(array $keys): void {
         foreach($keys as $key => $constant){
             if(!defined($key)){
                 define(strtoupper($key), $constant);
             }
         }
     }
}
