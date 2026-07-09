<?php
namespace SaQle\Build\Utils;

use SaQle\Core\Registries\ComponentRegistry;
use RuntimeException;

class TemplateCompiler {

     public static function compile(){
         $components = ComponentRegistry::all();
         $updated_components = [];

         foreach($components as $name => $c){

             $name_parts = explode(".", $name);
             $main_name = end($name_parts);

             $real_template_path = ComponentRegistry::real_template_path($c['template_path'], $c['owner']);
             $c['compiled_template_path'] = $real_template_path ? self::compile_template($real_template_path) : "";

             foreach($c['template_variations'] as $template_name => $template_config){
                 if($template_name == $main_name){
                     $c['template_variations'][$template_name]['compiled_template_path'] = $c['compiled_template_path'];
                 }else{
                     $var_real_template_path = ComponentRegistry::real_template_path(
                         $c['template_variations'][$template_name]['template_path'], 
                         $c['owner']
                     );
                     $c['template_variations'][$template_name]['compiled_template_path'] = $var_real_template_path ? self::compile_template($var_real_template_path) : "";
                 }
             }

             $updated_components[$name] = $c;
         }
         
         ComponentCompiler::cache_components($updated_components);
     }

     private static function cache_path(){
         $path = path_join([config('base_path'), config('templates_cache_dir')]);

         if(!is_dir($path)){
             mkdir($path, 0777, true);
         }

         return $path;
     }

     private static function compile_template(
         string $template_path = "", 
         string $content = "", 
         string $type = ""
     ) : string {

         $template_path = trim($template_path);
         $content = trim($content);

         if(!$template_path && !$content){
             return "";
         }

         if($template_path && !file_exists($template_path)){
             throw new RuntimeException("The template file: {$template_path} doesn't exist!");
         }

         if($template_path){
             $hash = md5($template_path);
             $filename = pathinfo($template_path, PATHINFO_FILENAME);
         }else{
             $hash = md5($content);
             $filename = "block";
         }

         $compiled_filename = "{$filename}_{$hash}.php";
         $compiled_absolute_path = path_join([self::cache_path(), $compiled_filename]);
         $compiled_relative_path = path_join([config('templates_cache_dir'), $compiled_filename]);

         if($template_path && file_exists($compiled_absolute_path) && filemtime($compiled_absolute_path) > filemtime($template_path)){
             //return $compiled_path;
         }

         $content = $content ? $content : $content = file_get_contents($template_path);

         //raw echo
         $content = preg_replace('/{!!\s*(.*?)\s*!!}/s', '<?php echo $1; ?>', $content);

         //escaped echo
         $content = preg_replace(
             '/{{\s*(.*?)\s*}}/s',
             "<?php echo htmlspecialchars($1, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'); ?>",
             $content
         );

         //directives
         $content = self::compile_directives($content);

         if(!$type){
             //component tags
             $content = self::compile_component_tags($content);

             //component blocks
             $content = self::compile_component_blocks($content);
         }

         file_put_contents($compiled_absolute_path, $content);

         return $compiled_relative_path;
     } 

     private static function parse_parentheses($text, $start_pos){
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

     private static function compile_directives($template){
         
         $output = '';
         $length = strlen($template);

         for($i = 0; $i < $length; $i++){
             if($template[$i] === '@'){

                 if(substr($template, $i, 4) === '@if('){
                     $parsed = self::parse_parentheses($template, $i + 3);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= "<?php if(($expr)): ?>";
                     continue;
                 }

                 if(substr($template, $i, 9) === '@foreach('){
                     $parsed = self::parse_parentheses($template, $i + 8);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= "<?php foreach($expr): ?>";
                     continue;
                 }

                 if(substr($template, $i, 7) === '@elseif'){
                     $parsed = self::parse_parentheses($template, $i + 7);

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

                 if(substr($template, $i, 5) === '@let('){
                     $parsed = self::parse_parentheses($template, $i + 4);

                     $expr = $parsed['expression'];
                     $i = $parsed['end'];

                     $output .= self::compile_let($expr);
                     continue;
                 }
             }

             $output .= $template[$i];
         }

         return $output;
     }

     private static function compile_component_tags(string $template) : string {

         $pattern = '/
            <ui:(component|form)
            \s*
            (
                (?:
                    [^<"\'>]
                    |
                    "(?:\\\\.|[^"\\\\])*"
                    |
                    \'(?:\\\\.|[^\'\\\\])*\'
                )*
            )
            (?:
                \/>

                |

                >
                (.*?)
                <\/ui:\1>
            )
         /isx';

         return preg_replace_callback(
             $pattern,
             [static::class, 'compile_component'],
             $template
         );
     }

     private static function compile_component(array $matches) : string {

         $type = strtolower(trim($matches[1]));
         $attribute_string = trim($matches[2]);
         $body = trim($matches[3] ?? '');

         $attributes = self::parse_attributes($attribute_string);

         $name = isset($attributes['name']) ? self::strip_quotes($attributes['name']) : null;

         if(!$name){
             return '<!-- component missing name -->';
         }

         $compiled_props = self::compile_props($attributes);

         if($type === 'form'){
             $name = 'saqle.autoresource';
         }

         $blocks = [];
         if($body){
             $blocks = self::extract_blocks($body);
         }

         $compiled_blocks = self::compile_override_blocks($blocks);

         return "<?php echo \$__renderer->component('".$name."', ".$compiled_props.", ".$compiled_blocks.", \$__context); ?>";
     }

     private static function extract_blocks(string &$content) : array {
         $blocks = [];

         $pattern = '/<block\s+name=([\'"])(.*?)\1\s*>(.*?)<\/block>/isx';

         $content = preg_replace_callback(
             $pattern,
             function($matches) use (&$blocks){
                 $blocks[trim($matches[2])] = trim($matches[3]);
                 return '';
             },
             $content
         );

         return $blocks;
     }

     private static function compile_override_blocks(array $blocks) : string {
         $compiled = [];

         foreach($blocks as $name => $content){
             $path = self::compile_template(
                 template_path: "", 
                 content: $content,
                 type: "override_block"
             );
             $compiled[] = var_export($name, true).' => '.var_export($path, true);
         }

         return '['.implode(',', $compiled).']';
     }

     private static function compile_component_blocks(string $template) : string {

         $pattern = '/<block\s+name=([\'"])(.*?)\1\s*>(.*?)<\/block>/isx';

         return preg_replace_callback(
             $pattern,
             function($matches){
                 $name = trim($matches[2]);
                 $content = trim($matches[3]);

                 $path = self::compile_template(
                     template_path: "", 
                     content: $content, 
                     type: 'component_block'
                 );

                 return "<?php echo \$__renderer->block(
                     ".var_export($name, true).",
                     ".var_export($path, true).",
                    get_defined_vars()
                 ); ?>";
             }, 
            
             $template
         );
     }

     private static function parse_attributes(string $attribute_string) : array {

         $pattern = '/
         ([:@]?[a-zA-Z_][a-zA-Z0-9_\-]*)
         \s*=\s*
         (
            "(?:\\\\.|[^"])*"
            |
            \'(?:\\\\.|[^\'])*\'
         )
         /x';

         preg_match_all($pattern, $attribute_string, $matches, PREG_SET_ORDER);

         $attributes = [];

         foreach($matches as $match){
             $key = trim($match[1]);
             $value = trim($match[2]);
             $attributes[$key] = $value;
         }

         return $attributes;
     }

     private static function compile_props(array $attributes) : string {

         $compiled = [];

         foreach($attributes as $key => $value){

             /*
             -----------------------------------
             BOUND PROP
             :field="$field"
             -----------------------------------
             */

             if(str_starts_with($key, ':')){
                 $prop_name = substr($key, 1);
                 $compiled[] = "'".$prop_name."' => ".self::strip_quotes($value);

                 continue;
             }

             /*
             -----------------------------------
             LITERAL PROP
             title="Hello"
             -----------------------------------
             */

             $compiled[] = "'".$key."' => ".$value;
         }

         return '['.implode(', ', $compiled).']';
     }

     private static function strip_quotes(string $value) : string {

         $quote = substr($value, 0, 1);

         if(
             ($quote === '"' || $quote === "'")
             &&
             substr($value, -1) === $quote
         ){
             return substr($value, 1, -1);
         }

         return $value;
     }

     private static function split_assignments(string $expression): array {
         $parts = [];
         $current = '';

         $paren = 0;
         $bracket = 0;
         $brace = 0;

         $quote = null;
         $escaped = false;

         $length = strlen($expression);

         for ($i = 0; $i < $length; $i++) {
             $ch = $expression[$i];

             if ($quote !== null) {
                $current .= $ch;

                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($ch === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($ch === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $quote = $ch;
                $current .= $ch;
                continue;
            }

            switch ($ch) {
                case '(':
                    $paren++;
                    break;

                case ')':
                    $paren--;
                    break;

                case '[':
                    $bracket++;
                    break;

                case ']':
                    $bracket--;
                    break;

                case '{':
                    $brace++;
                    break;

                case '}':
                    $brace--;
                    break;

                case ',':
                    if (
                        $paren === 0 &&
                        $bracket === 0 &&
                        $brace === 0
                    ) {
                        $parts[] = trim($current);
                        $current = '';
                        continue 2;
                    }
                    break;
            }

            $current .= $ch;
         }

         if (trim($current) !== '') {
            $parts[] = trim($current);
         }

         return $parts;
     }

     private static function compile_let(string $expression): string {
         $assignments = self::split_assignments($expression);
         
         $php = "<?php ";

         foreach($assignments as $assignment){
             $php .= trim($assignment) . "; ";
         }

         $php .= "?>";

         return $php;
     }
}