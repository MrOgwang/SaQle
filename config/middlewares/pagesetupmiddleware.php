<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The web setup setup middleware
 * - Adds global styles, scripts and meta tags to the page for web requests
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Config\Middlewares;

use SaQle\Middleware\IMiddleware;
use SaQle\Middleware\MiddlewareRequestInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use SaQle\App;

class PageSetupMiddleware extends IMiddleware{
     public function handle(MiddlewareRequestInterface &$request){
         self::page_setup();
     	 parent::handle($request);
     }

     private static function page_setup(){
         $app         = App::init();
         $configdir   = $app::config()::getdirectory();
         $stylesfile  = $configdir.'/setup/styles.php';
         $scriptsfile = $configdir.'/setup/scripts.php';
         $metafile    = $configdir.'/setup/meta.php';

         if(file_exists($stylesfile)){
             $styles = require_once $stylesfile;
             $app::static()::link('css', $styles);
         }

         if(file_exists($scriptsfile)){
             $scripts = require_once $scriptsfile;
             $app::static()::link('js', $scripts);
         }

         if(file_exists($metafile)){
             $tags = require_once $metafile;
             $app::meta()::tags($tags);
         }
     }
}
?>