<?php

namespace SaQle\Views;

use SaQle\Auth\Models\Interfaces\IUser;

class View{

     private string $content;
     private array  $data;
     private IUser  $user;

     public function __construct(string $template, IUser $user, bool $isfile = true){
         $this->user    = $user;
         $this->content = $isfile ? $this->prune_template(file_get_contents($template)) : $this->prune_template($template);
     }

     /*private function prune_template($template){
         $user = $this->user;
         $pattern = '/@(can|cannot|is|isnot)\((.*?)\)(.*?)@end\1/s';

         while(preg_match_all($pattern, $template, $matches, PREG_OFFSET_CAPTURE)) {
             foreach (array_reverse($matches[0]) as $index => [$full_match, $full_offset]) {
                 $directive = $matches[1][$index][0]; // 'can' or 'is'
                 $arg = trim($matches[2][$index][0], '\'"');
                 $content = $matches[3][$index][0];

                 $allowed = match ($directive) {
                     'can'    => method_exists($user, 'can') && $user->can($arg),
                     'cannot' => method_exists($user, 'cannot') && $user->cannot($arg),
                     'is'     => method_exists($user, 'is') && $user->is($arg),
                     'isnot'  => method_exists($user, 'isnot') && $user->isnot($arg),
                     default  => false,
                 };

                 $replacement = $allowed ? $content : '';
                 $template = substr_replace($template, $replacement, $full_offset, strlen($full_match));
             }
         }

         return $template;
     }*/

     private function prune_template($template){
         $user = $this->user;
         $pattern = '/@(can|cannot|is|isnot)\((.*?)\)(.*?)@end\1/s';

         while (preg_match($pattern, $template, $matches, PREG_OFFSET_CAPTURE)) {
             $directive = $matches[1][0]; // 'can' or 'is'
             $arg = trim($matches[2][0], '\'"');
             $content = $matches[3][0];

             $allowed = match ($directive){
                 'can'    => method_exists($user, 'can') && $user->can($arg),
                 'cannot' => method_exists($user, 'cannot') && $user->cannot($arg),
                 'is'     => method_exists($user, 'is') && $user->is($arg),
                 'isnot'  => method_exists($user, 'isnot') && $user->isnot($arg),
                 default  => false,
             };

             $full_match = $matches[0][0];
             $template = str_replace($full_match, $allowed ? $content : '', $template);
         }

         return $template;
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

         $user = $this->user;
         //run the permissions and roles first

         //replace template syntax with php syntax
         $this->content = preg_replace('/{{\s+(.+?)\s+}}/', '<?php echo $1; ?>', $this->content);
         $this->content = preg_replace('/@if\(\s*(.+?)\s*\)/', '<?php if($1): ?>', $this->content);
         $this->content = preg_replace('/@elseif\(\s*(.+?)\s*\)/', '<?php elseif($1): ?>', $this->content);
         $this->content = str_replace('@else', '<?php else: ?>', $this->content);
         $this->content = str_replace('@endif', '<?php endif; ?>', $this->content);
         $this->content = preg_replace('/@foreach\(\s*(.+?)\s*\)/', '<?php foreach($1): ?>', $this->content);
         $this->content = str_replace('@endforeach', '<?php endforeach; ?>', $this->content);
         $this->content = preg_replace_callback('/@can\((.*?)\)/', function ($matches) use ($user){
            return "<?php if (\$user && \$user->can({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endcan', '<?php endif; ?>', $this->content);
         $this->content = preg_replace_callback('/@is\((.*?)\)/', function ($matches) use ($user) {
            return "<?php if (\$user && \$user->is({$matches[1]})): ?>";
         }, $this->content);
         $this->content = str_replace('@endis', '<?php endif; ?>', $this->content);

         ob_start();
         eval('?>'.$this->content);
         $final = ob_get_clean();

         return $final;
     }
}