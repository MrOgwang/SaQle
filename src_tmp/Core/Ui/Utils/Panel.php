<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Utils;

use SaQle\Core\Ui\Panels\PanelFieldsCompiler;
use SaQle\Core\Ui\Details\DetailFieldsCompiler;

trait Panel {
	 public private(set) string $model {
         set(string $value){
             $this->model = $value;
         }

         get => $this->model;
     }

     public private(set) array $props {
         set(array $value){
             $this->props = $value;
         }

         get => $this->props;
     }

     public private(set) mixed $data {
         set(mixed $value){
             $this->data = $value;
         }

         get => $this->data;
     }

     public private(set) array $columns {
         set(array $value){
             $this->columns = $value;
         }

         get => $this->columns;
     }

     public private(set) array $fk_columns {
         set(array $value){
             $this->fk_columns = $value;
         }

         get => $this->fk_columns;
     }

     protected array $fields;

     public function __construct(string $model, array $props, string $view){
         $this->model  = $model;
         $this->props  = $props;
         $this->extract_columns($view);
     }

     protected function extract_columns(string $view){  

         $this->fields = $view === "table" ? PanelFieldsCompiler::compile($this->model) : DetailFieldsCompiler::compile($this->model);

     	 $model_presenters = $this->model::get_presenters();

     	 $column_names = array_keys($this->fields);

     	 if(isset($this->props['presenter'])){
     	 	 $presenters = $model_presenters[$this->props['presenter']] ?? null;
     	 	 if($presenters){
     	 	 	 $column_names = array_keys($presenters);
     	 	 }
     	 }
     	 
         $columns = [];
         $fk_columns = [];

         $column_index = 0;
         foreach($column_names as $col_name){

             if(!array_key_exists($col_name, $this->fields)){
                 continue;
             }

             $columns[$col_name] = (Object)[
                 'label' => ucwords(str_replace(" ", "&nbsp;", $this->fields[$col_name]->label)),
                 'index' => $column_index,
                 'name' => $col_name,
                 'type' => $view === "table" ? $this->fields[$col_name]->ui_type : $this->fields[$col_name]->ui_group
             ];

             if($this->fields[$col_name]->is_fk){
                 $fk_columns[] = $col_name;
             }

             $column_index++;
         }

         $this->columns = $columns;
         $this->fk_columns = $fk_columns;
     }
}