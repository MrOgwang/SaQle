<?php

namespace SaQle\Core\Ui;

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
                return "<link rel='stylesheet' href='{$a}'>"; $n * 2;
             }, $assets);
         }

         return array_map(function($a){
            return "<script src='{$a}'></script>";
         }, $assets);

     }

     public static function output(): array {

         $cache_path = path_join([config('base_path'), config('assets_cache_dir')]);

         if(!is_dir($cache_path)){
             mkdir($cache_path, 0777, true);
         }

         $css_files = self::build(self::$css, 'css', $cache_path);
         $js_files  = self::build(self::$js, 'js', $cache_path);

         return [
            'css' => $css_files ? implode("\n", self::assets_to_links($css_files, "css")) : '',
            'js'  => $js_files ? implode("\n", self::assets_to_links($js_files, "js")) : ''
         ];
     }

     private static function build(array $files, string $type, string $path): array {

         $assets = [];

         foreach($files as $file){
             $hash = md5($file);
             $filename = pathinfo($file, PATHINFO_FILENAME);
             $output_filename = "{$filename}_{$hash}";
             $output_path = path_join([$path, $output_filename.".{$type}"]);

             if(!file_exists($output_path)){
                 $content = self::minify(file_get_contents($file));
                 file_put_contents($output_path, $content);
             }

             $assets[] = config("static_assets_route")."/{$type}/{$output_filename}";
         }

         return $assets;
     }

     private static function minify($content){
         //simple minifier: TODO, upgrade minifier later
         return preg_replace('/\s+/', ' ', $content);
     }
}