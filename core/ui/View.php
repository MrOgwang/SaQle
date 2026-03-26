<?php

namespace SaQle\Core\Ui;

class View {

     private string $template_path;
     private array $data = [];

     public function __construct(string $template){
         $this->template_path = $template;
     }

     public function set_context(array $data){
         $this->data = $data;
     }

     public function render(){
         $compiler = new TemplateCompiler();
         $compiled = $compiler->compile($this->template_path);

         extract($this->data, EXTR_SKIP);

         ob_start();
         include $compiled;
         return ob_get_clean();
     }
}