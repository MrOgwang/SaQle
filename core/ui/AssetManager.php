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

     public static function output(): array {

         $cache_path = path_join([config('base_path'), config('assets_cache_dir')]);

         if(!is_dir($cache_path)){
             mkdir($cache_path, 0777, true);
         }

         $css_file = self::build(self::$css, 'css', $cache_path);
         $js_file  = self::build(self::$js, 'js', $cache_path);

         return [
            'css' => $css_file ? "<link rel='stylesheet' href='{$css_file}'>" : '',
            'js'  => $js_file ? "<script src='{$js_file}'></script>" : ''
         ];
     }

     private static function build(array $files, string $type, string $path): ?string {

         if(empty($files)) return null;

         $hash = md5(implode('|', $files));
         $filename = "app_{$hash}";
         $filename2 = "app_{$hash}.{$type}";
         $output = path_join([$path, $filename2]);

         if(!file_exists($output)){

             $content = '';

             foreach(array_unique($files) as $file){
                 $content .= file_get_contents($file)."\n";
             }

             $content = self::minify($content);

             file_put_contents($output, $content);
         }

         return config("static_assets_route")."/{$type}/{$filename}";
     }

     private static function minify($content){
         // simple minifier (can upgrade later)
         return preg_replace('/\s+/', ' ', $content);
     }
}