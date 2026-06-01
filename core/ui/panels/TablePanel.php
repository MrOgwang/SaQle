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
         $fields = FormFieldsCompiler::compile($this->model_class);
         $columns = [];

         foreach($fields as $field_name => $field){
             $columns[$field_name] = (Object)[
                 'label' => ucwords(str_replace(" ", "&nbsp;", $field->label))
             ];
         }

         $this->columns = $columns;
     }

     protected function fetch_table_data(){

         $model = $this->model_class;

         $page = $this->props['pagination']['page'] ?? 1;
         $records = $this->props['pagination']['records'] ?? 100;
         
         $this->data = $model::get()->paginate(page: $page, count: $records)->all();
         $this->paginator = $this->data->paginator;
     }
}