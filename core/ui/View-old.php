<?php

namespace SaQle\Core\Ui;

use SaQle\Auth\Interfaces\UserInterface;
use DOMDocument;

class View {

     private string $content;

     private array  $data;

     public function __construct(string $template, bool $isfile = true){
         $this->content = $isfile ? file_get_contents($template) : $template;
     }

     public function set_context(array $data){
         $this->data = $data;
     }

     public function get_template(){
         return $this->content;
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
         $meta = "";

         if(preg_match('/@meta(.*?)@endmeta/s', $this->content, $matches)){
             $meta = trim($matches[1]);
             $this->content = preg_replace('/@meta(.*?)@endmeta/s', '', $this->content);
         } 

         return $meta;
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
         $path  = config('app.root_domain')."static/css/";
         $links = [];
         foreach ($names as $n){
             $css_file_path = $path.$n.".css";
             $links[] = "<link href='{$css_file_path}' rel='stylesheet'>";
         }
         return $links;
     }
     private function js_names_to_links(array $names){
         $path = config('app.root_domain')."static/js/";
         $links = [];
         foreach ($names as $n){
             $js_file_path = $path.$n.".js";
             $links[] = "<script src='{$js_file_path}'></script>";
         }
         return $links;
     }

     private function parse_parentheses($text, $start_pos){
         $depth = 0;
         $length = strlen($text);

         for($i = $start_pos; $i < $length; $i++){
             if($text[$i] === '('){
                 $depth++;
             }

             if($text[$i] === ')'){
                 $depth--;

                 if($depth === 0){
                     return [
                        'expression' => substr($text, $start_pos + 1, $i - $start_pos - 1),
                        'end' => $i
                     ];
                 }
             }
         }

         return null;
     }

     private function compile_directives($template){
         $output = '';
         $length = strlen($template);

         for($i = 0; $i < $length; $i++){
             if($template[$i] === '@'){
                 if(substr($template, $i, 4) === '@if('){
                     $parsed = $this->parse_parentheses($template, $i + 3);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= "<?php if(($expr)): ?>";
                     continue;
                 }

                 if(substr($template, $i, 9) === '@foreach('){
                     $parsed = $this->parse_parentheses($template, $i + 8);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= "<?php foreach($expr): ?>";
                     continue;
                 }

                 if(substr($template, $i, 7) === '@elseif'){
                     $parsed = $this->parse_parentheses($template, $i + 7);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= "<?php elseif(($expr)): ?>";
                     continue;
                 }

                 if(substr($template, $i, 5) === '@else'){
                     $output .= "<?php else: ?>";
                     $i += 4;
                     continue;
                 }

                 if(substr($template, $i, 6) === '@endif'){
                     $output .= "<?php endif; ?>";
                     $i += 5;
                     continue;
                 }

                 if(substr($template, $i, 11) === '@endforeach'){
                     $output .= "<?php endforeach; ?>";
                     $i += 10;
                     continue;
                 }
             }

             $output .= $template[$i];
         }

         return $output;
     }

     public function view(){
         extract($this->data, EXTR_SKIP);

         //raw output
         $this->content = preg_replace('/{!!\s*(.*?)\s*!!}/s', '<?php echo $1; ?>', $this->content);

         //escaped/normal output
         $this->content = preg_replace(
             '/{{\s*(.*?)\s*}}/s', 
             "<?php echo htmlspecialchars($1, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>", 
             $this->content
         );

         $this->content = $this->compile_directives($this->content);
         
         ob_start();
         eval('?>' . $this->content);
         return ob_get_clean();
     }
}