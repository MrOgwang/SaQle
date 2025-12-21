<?php

namespace SaQle\Views;

use SaQle\Auth\Models\Interfaces\IUser;
use DOMDocument;

class View{

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

     public function get_forms(){
         libxml_use_internal_errors(true);

         $dom = new DOMDocument('1.0', 'UTF-8');

         // Important flags:
         // - NOIMPLIED: prevents <html><body> wrapping
         // - NODEFDTD: prevents <!DOCTYPE> injection
         $dom->loadHTML($this->content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

         libxml_clear_errors();

         $forms = [];

         /** @var DOMElement $node */
         foreach ($dom->getElementsByTagName('autoform') as $node) {
             $form = [];

             foreach ($node->attributes as $attr) {
                $form[$attr->name] = $attr->value;
             }

             $forms[] = $form;
         }

         return $forms;
     }

     /*public function get_forms(){
          $forms = [];

         //1. Capture @form ... @endform blocks
         $pattern = '/@form\s+(.*?)\s*@endform/s';
         preg_match_all($pattern, $this->content, $matches, PREG_SET_ORDER);

         foreach ($matches as $match) {
             $attributeString = trim($match[1]);

             // 2. Parse attributes inside @form
             $attributes = $this->parse_form_attributes($attributeString);

             // 3. Add the parsed form definition
             $forms[] = $attributes;
         }

         return $forms;
     }

     private function parse_form_attributes(string $attributeString): array{
         $attributes = [];

         //Match name='value' OR name="value"
         $pattern = '/(\w+)\s*=\s*(\'[^\']*\'|"[^"]*")/';

         preg_match_all($pattern, $attributeString, $matches, PREG_SET_ORDER);

         foreach ($matches as $match) {
             $key = $match[1];
             $val = trim($match[2], '\'"'); // remove quotes
             $attributes[$key] = $val;
         }

         return $attributes;
     }*/


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
         /*$this->content = preg_replace("/{{\s*(.+?)\s*}}/", "<?php echo htmlspecialchars($1, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', false); ?>", $this->content);*/
         $this->content = preg_replace("/{{\s*(.+?)\s*}}/", "<?php echo $1; ?>", $this->content);
         $this->content = preg_replace('/{!!\s*(.+?)\s*!!}/', '<?php echo $1; ?>', $this->content);
         $this->content = preg_replace('/@if\(\s*(.+?)\s*\)/', '<?php if($1): ?>', $this->content);
         $this->content = preg_replace('/@elseif\(\s*(.+?)\s*\)/', '<?php elseif($1): ?>', $this->content);
         $this->content = str_replace('@else', '<?php else: ?>', $this->content);
         $this->content = str_replace('@endif', '<?php endif; ?>', $this->content);
         $this->content = preg_replace('/@foreach\(\s*(.+?)\s*\)/', '<?php foreach($1): ?>', $this->content);
         $this->content = str_replace('@endforeach', '<?php endforeach; ?>', $this->content);
         
         $this->content = preg_replace_callback('/@cannot\((.*?)\)/', function ($matches) use ($session_user){
            return "<?php if (\$session_user && \$session_user->cannot({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endcannot', '<?php endif; ?>', $this->content);

         $this->content = preg_replace_callback('/@can\((.*?)\)/', function ($matches) use ($session_user){
            return "<?php if (\$session_user && \$session_user->can({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endcan', '<?php endif; ?>', $this->content);

         $this->content = preg_replace_callback('/@isnot\((.*?)\)/', function ($matches) use ($session_user) {
            return "<?php if (\$session_user && \$session_user->isnot({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endisnot', '<?php endif; ?>', $this->content);

         $this->content = preg_replace_callback('/@is\((.*?)\)/', function ($matches) use ($session_user) {
            return "<?php if (\$session_user && \$session_user->is({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endis', '<?php endif; ?>', $this->content);

         $this->content = preg_replace_callback('/@hasnot\((.*?)\)/', function ($matches) use ($session_user) {
            return "<?php if (\$session_user && \$session_user->hasnot({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endhasnot', '<?php endif; ?>', $this->content);

         $this->content = preg_replace_callback('/@has\((.*?)\)/', function ($matches) use ($session_user) {
            return "<?php if (\$session_user && \$session_user->has({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endhas', '<?php endif; ?>', $this->content);
         
         ob_start();
         eval('?>'.$this->content);
         $final = ob_get_clean();

         return $final;
     }
}