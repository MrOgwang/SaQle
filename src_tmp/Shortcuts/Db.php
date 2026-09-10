<?php

use SaQle\Core\Support\Db;

if(!function_exists('system_connection')){
     function system_connection() : string {
         return "default.system";
     }
}