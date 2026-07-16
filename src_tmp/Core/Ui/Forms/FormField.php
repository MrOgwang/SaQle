<?php
namespace SaQle\Core\Ui\Forms;

use RuntimeException;
use SaQle\Core\Ui\Forms\Form;
use SaQle\Core\Ui\Utils\Label;

class FormField {

     private array $attributes = [];

     private ?array $source = null;

     private ?array $scope = null;

     public private(set) string $state = "default" {
         set(string $value){
             $this->state = $value;
         }

         get => $this->state;
     }

     public private(set) string $ui_type = "normal" {
         set(string $value){
             $this->ui_type = $value;
         }

         get => $this->ui_type;
     }

     public function __construct(
         array  $attrs, 
         string $ui_type = 'normal', 
         string $state = 'default'
     ){
         $this->attributes = $attrs;
         $this->state = $state;
         $this->ui_type = $ui_type;
     }

     private function render_data_attrs(): string {

         $data = $this->attributes['data'] ?? [];

         $html = '';

         foreach($data as $key => $value){

             if($value === null || $value === false){
                 continue;
             }

             if($value === true){
                 $html .= " data-{$key}";
             }else{
                 $html .= " data-{$key}='{$value}'";
             }
         }

         return $html;
     }

     public function __get($key){

         if(!array_key_exists($key, $this->attributes)){
             return "";
         }

         if($key === 'data'){
             return $this->render_data_attrs();
         }

         return $this->attributes[$key];
     }

     public function __call($method, $args){
         //fluent setter
         if(count($args) === 1){
             $this->attributes[$method] = $args[0];
             
             return $this;
         }

         //boolean flags
         if(count($args) === 0){
             $this->attributes[$method] = true;

             return $this;
         }

         return $this;
     }

     public function get_attributes(){
         return $this->attributes;
     }

     public function class(string ...$classes): static {

         $existing = $this->attributes['class'] ?? '';

         $all = array_filter(array_merge(
             preg_split('/\s+/', trim($existing)) ?: [],
             $classes
         ));

         $this->attributes['class'] = implode(' ', array_unique($all));

         return $this;
     }

     private function format_data_key(string $key) : string {
         return preg_replace('/[^a-z0-9\-_]/', '-', strtolower(trim($key)));
     }

     private function append_to_data(array &$data, string $key, mixed $val){
         if(array_key_exists($key, $data)){
             $data[$key] = $data[$key].",".$val;
         }else{
             $data[$key] = $val;
         }
     }

     public function data(string|array $key, mixed $value = null, bool $append = false) : static {

         $existing = $this->attributes['data'] ?? [];

         if(is_array($key)){
             foreach($key as $k => $v){
                 $formated_key = $this->format_data_key($k);

                 $append ? 
                 $this->append_to_data($existing, $formated_key, $v) :
                 $existing[$formated_key] = $v;
             }
         }else{
             $formated_key = $this->format_data_key($key);

             $append ? 
             $this->append_to_data($existing, $formated_key, $value) :
             $existing[$formated_key] = $value;
         }

         $this->attributes['data'] = $existing;

         return $this;
     }

     public function choices(
         array $choices, 
         bool $append = false
     ){
         if(!$append){
             $this->attributes['choices'] = $choices;
             $this->attributes['type'] = 'select';
         }else{
             $this->attributes['choices'] = array_merge($this->attributes['choices'] ?? [], $choices);
             $this->attributes['type'] = 'select';
         }

         return $this;
     }

     /**
      * The source query customizes how related data
      * is fetched from the db
      * */
     public function query(callable $source_query){
         if(!$this->source){
             return $this;
         }

         $this->source['query'] = $source_query;

         return $this;
     }

     public function source(?array $source = null){
         $this->source = $source;
     }

     public function get_source() : ?array {
         return $this->source;
     }

     public function is_relation() : bool {
         return $this->source ? true : false;
     }

     public function scoped_to(Form $form, array $fields){
         
         if(!$fields){
             return $this;
         }
         
         $classes = $this->attributes['class'] ?? '';
         if(str_contains($classes, 'opts_deferred')){
             $classes = trim(str_replace('opts_deferred', '', $classes));
             $this->attributes['class'] = $classes;
         }

         foreach($fields as $f){
             $form->field($f)->class('opts_cascade')->data('cfields', $this->name, true);
         }

         $this->scope = $fields;
     }

     public function get_scope() : ?array {
         return $this->scope;
     }

     public function is_scoped() : bool {
         return $this->scope ? true : false;
     }

     public function searchable(array $search_fields){
         if(!$this->source || !$search_fields){
             return $this;
         }

         $this->attributes['searchable'] = $search_fields;
         $this->attributes['placeholder'] = ucfirst(
             strtolower("Search ".$this->label." by "
                 .natural_join(array_map(fn($f) => Label::make($f), $search_fields), 'or')
             )
         );

         return $this;
     }

     public function is_searchable() : bool {
         return array_key_exists('searchable', $this->attributes);
     }
}
