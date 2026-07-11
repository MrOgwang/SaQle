<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Panels;

use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Core\Ui\Forms\FormFieldsCompiler;
use SaQle\Orm\Entities\Model\Collection\Paginator;

final class TablePanel {

     public private(set) string $model_class {
         set(string $value){
             $this->model_class = $value;
         }

         get => $this->model_class;
     }

     public private(set) array $columns {
         set(array $value){
             $this->columns = $value;
         }

         get => $this->columns;
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

     public private(set) ?Paginator $paginator {
         set(?Paginator $value){
             $this->paginator = $value;
         }

         get => $this->paginator;
     }

     public function __construct(string $model_class, array $props = []){
         $this->model_class = $model_class;
         $this->props = $props;
         $this->extract_table_columns();
         $this->fetch_table_data();
     }

     protected function extract_table_columns(){

         $presenters = $this->model_class::get_presenters($this->props['presenter'] ?? null);

         
         $fields = FormFieldsCompiler::compile($this->model_class);
         $column_names = $presenters ? array_keys($presenters) : array_keys($fields);
         $columns = [];

         $column_index = 0;
         foreach($column_names as $col_name){

             if(!array_key_exists($col_name, $fields)){
                 continue;
             }

             $field = $fields[$col_name];
             $columns[$col_name] = (Object)[
                 'label' => ucwords(str_replace(" ", "&nbsp;", $field->label)),
                 'index' => $column_index,
                 'name' => $col_name,
                 'type' => $field->ui_type
             ];
             $column_index++;
         }

         $this->columns = $columns;
     }

     protected function fetch_table_data(){

         $model = $this->model_class;

         $page = $this->props['pagination']['page'] ?? 1;
         $records = $this->props['pagination']['records'] ?? 100;
         $search = trim($this->props['search'] ?? "");

         $manager = $model::get()->paginate(page: $page, count: $records);
         if($search){
             $columns = array_values($this->columns);
             foreach($columns as $index => $col){
                 if($index === 0){
                     $manager->where($col->name."__contains", $search);
                 }else{
                     $manager->or_where($col->name."__contains", $search);
                 }
             }
         }
         
         $this->data = $manager->all()->present('admin');

         $this->paginator = $this->data->paginator;
     }
}