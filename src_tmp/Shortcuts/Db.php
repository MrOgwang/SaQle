<?php

use SaQle\Core\Support\Db;

if(!function_exists('system_connection')){
     function system_connection() : string {

         $system_db = Db::get_system_db();

         return $system_db[0].".".$system_db[1];
     }
}