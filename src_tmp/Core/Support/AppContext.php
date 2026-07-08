<?php

namespace SaQle\Core\Support;

use SaQle\App;

final class AppContext{
     private static App $app;

     public static function set(App $app): void {
         self::$app = $app;
     }

     public static function get(): App {
         return self::$app;
     }
}
