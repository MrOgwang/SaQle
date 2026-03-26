<?php

namespace SaQle\Core\Ui;

class TemplateCompiler {

     private string $cache_path;

     public function __construct() {
         $this->cache_path = path_join([config('base_path'), config('templates_cache_dir')]);

         if(!is_dir($this->cache_path)){
             mkdir($this->cache_path, 0777, true);
         }
     }

     public function compile(string $template_path) : string {
         $hash = md5($template_path);
         $filename = pathinfo($template_path, PATHINFO_FILENAME);
         $compiled = "{$this->cache_path}/{$filename}_{$hash}.php";

         if(!file_exists($compiled) || filemtime($compiled) < filemtime($template_path)){

             $content = file_get_contents($template_path);

             //raw echo
             $content = preg_replace('/{!!\s*(.*?)\s*!!}/s', '<?php echo $1; ?>', $content);

             //escaped echo
             $content = preg_replace(
                 '/{{\s*(.*?)\s*}}/s',
                 "<?php echo htmlspecialchars($1, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>",
                 $content
             );

             //directives
             $content = $this->compile_directives($content);

             file_put_contents($compiled, $content);
         }

         return $compiled;
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
}