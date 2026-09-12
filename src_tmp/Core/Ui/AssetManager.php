<?php

namespace SaQle\Core\Ui;

use SaQle\Auth\Context\ActorContext;

class AssetManager {

     private static array $css = [];

     private static array $js  = [];

     public static function add_css(array $files = []){
         self::$css = array_merge(self::$css, $files);
     }

     public static function add_js(array $files = []){
         self::$js = array_merge(self::$js, $files);
     }

     private static function assets_to_links(array $assets, string $type){

         if($type === "css"){
             return array_map(function($a){
                return "<link rel='stylesheet' href='{$a}'>";
             }, $assets);
         }

         return array_map(function($a){
            return "<script src='{$a}'></script>";
         }, $assets);

     }

     public static function output(): array {

         $js_cache_path = path_join([config('base_path'), config('assets_cache_dir'), "js"]);
         $css_cache_path = path_join([config('base_path'), config('assets_cache_dir'), "css"]);

         if(!is_dir($js_cache_path)){
             mkdir($js_cache_path, 0777, true);
         }
         if(!is_dir($css_cache_path)){
             mkdir($css_cache_path, 0777, true);
         }

         $css_files = self::build(self::$css, 'css', $css_cache_path);
         $js_files  = self::build(self::$js, 'js', $js_cache_path);

         return [
            'css' => $css_files ? implode("\n", self::assets_to_links($css_files, "css")) : '',
            'js'  => $js_files ? implode("\n", self::assets_to_links($js_files, "js")) : ''
         ];
     }

     private static function build(array $assets, string $type, string $path): array {

         $files = [];

         foreach($assets as $asset){

             $file = $asset->file;
             $name = $asset->name;

             if(str_starts_with($file, '~')){
                 $files[] = ltrim($file, '~');

                 continue;
             }

             $filename = $name ? pathinfo($name, PATHINFO_FILENAME) : pathinfo($file, PATHINFO_FILENAME);
             $output_path = path_join([$path, $filename.".{$type}"]);

             $environment = config('environment', 'development');

             if($environment === 'development' || !file_exists($output_path)){
                 $content = self::minify(file_get_contents($file));
                 file_put_contents($output_path, $content);
             }

             $prefix = ActorContext::is_platform() ? '/saqle' : '';
             
             $files[] = $prefix.config("static_assets_route")."/{$type}/{$filename}";
         }

         return $files;
     }

     private static function minify($content){
         //simple minifier: TODO, upgrade minifier later
         return preg_replace('/\s+/', ' ', $content);
     }
}