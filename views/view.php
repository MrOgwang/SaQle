<?php

namespace SaQle\Views;

class View{

     private string $content;
     private array  $data;

     public function __construct(string $template, bool $isfile = true){
         $this->content = $isfile ? file_get_contents($template) : $template;
     }

     public function set_context(array $data){
         $this->data = $data;
     }

     public function get_blocks(){
         $pattern = '/@block(.*?)@endblock/s';
         $blocks  = [];

         $this->content = preg_replace_callback($pattern, function($matches) use (&$blocks){
             $blocks[] = trim($matches[1]);
             return "{{ $".trim($matches[1])." }}";
         }, $this->content);

         return $blocks;
     }

     public function get_css(){
         $css = [];

         if(preg_match('/@css(.*?)@endcss/', $this->content, $matches)){
             $cssline = trim($matches[1]);
             $this->content = preg_replace('/@css(.*?)@endcss/', '', $this->content);
             $css = explode(",", $cssline);
         } 

         return $this->css_names_to_links($css);
     }

     public function get_js(){
         $js = [];

         if(preg_match('/@js(.*?)@endjs/', $this->content, $matches)){
             $jsline = trim($matches[1]);
             $this->content = preg_replace('/@js(.*?)@endjs/', '', $this->content);
             $js = explode(",", $jsline);
         } 

         return $this->js_names_to_links($js);
     }

     public function get_meta(){
         $meta = [];

         if(preg_match('/@meta(.*?)@endmeta/s', $this->content, $matches)){
             $metaline = trim($matches[1]);
             $this->content = preg_replace('/@meta(.*?)@endmeta/s', '', $this->content);
             $meta = explode(",", $metaline);
         } 

         return $meta;
     }

     public function get_default(){
         $default = '';

         if(preg_match('/@content(.*?)@endcontent/', $this->content, $matches)){
             $default = trim($matches[1]);
         }

         return $default;
     }

     public function get_title(){
         $title = "";

         if(preg_match('/@title(.*?)@endtitle/', $this->content, $matches)){
             $title = trim($matches[1]);
             $this->content = preg_replace('/@title(.*?)@endtitle/', '', $this->content);
         } 

         return $title;
     }

     private function css_names_to_links(array $names){
         $path  = ROOT_DOMAIN."static/css/";
         $links = [];
         foreach ($names as $n){
             $css_file_path = $path.$n.".css";
             $links[] = "<link href='{$css_file_path}' rel='stylesheet'>";
         }
         return $links;
     }
     private function js_names_to_links(array $names){
         $path = ROOT_DOMAIN."static/js/";
         $links = [];
         foreach ($names as $n){
             $js_file_path = $path.$n.".js";
             $links[] = "<script src='{$js_file_path}'></script>";
         }
         return $links;
     }

     public function view(){
         //extract the data
         extract($this->data);

         //replace template syntax with php syntax
         $this->content = preg_replace('/{{\s+(.+?)\s+}}/', '<?php echo $1; ?>', $this->content);
         $this->content = preg_replace('/@if\(\s*(.+?)\s*\)/', '<?php if($1): ?>', $this->content);
         $this->content = preg_replace('/@elseif\(\s*(.+?)\s*\)/', '<?php elseif($1): ?>', $this->content);
         $this->content = str_replace('@else', '<?php else: ?>', $this->content);
         $this->content = str_replace('@endif', '<?php endif; ?>', $this->content);
         $this->content = preg_replace('/@foreach\(\s*(.+?)\s*\)/', '<?php foreach($1): ?>', $this->content);
         $this->content = str_replace('@endforeach', '<?php endforeach; ?>', $this->content);

         ob_start();
         eval('?>'.$this->content);
         $final = ob_get_clean();

         return $final;
     }
}