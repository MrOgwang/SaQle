<?php

declare(strict_types=1);

namespace SaQle\Core\Ui\Panels;

use SaQle\Orm\Entities\Model\Collection\Paginator;
use SaQle\Core\Ui\Utils\Panel;

final class TableView {

     use Panel {
         Panel::__construct as private __panelConstruct;
     }

     public private(set) ?Paginator $paginator {
         set(?Paginator $value){
             $this->paginator = $value;
         }

         get => $this->paginator;
     }

     public function __construct(string $model, array $props = []){
         $this->__panelConstruct($model, $props, 'table');
         $this->fetch_table_data();
     }

     protected function fetch_table_data(){

         $page = $this->props['pagination']['page'] ?? 1;
         $records = $this->props['pagination']['records'] ?? 100;
         $search = trim($this->props['search'] ?? "");
         $filter = $this->props['filter'] ?? [];

         $query = $this->model::get();

         if($this->fk_columns){
             $query->with($this->fk_columns);
         }
         
         if($search){
             $columns = array_values($this->columns);
             foreach($columns as $index => $col){
                 if($index === 0){
                     $query->where($col->name."__contains", $search);
                 }else{
                     $query->or_where($col->name."__contains", $search);
                 }
             }
         }

         if($filter){
             foreach($filter as $f){
                 $fp = explode(":", $f);
                 $query->where($fp[0]."__".$fp[1], $fp[2]);
             }
         }

         if($this->model::has_soft_delete()){
             $query->where(config('model.is_removed_column')."__eq", 0);
         }
         
         $this->data = $query->paginate(page: $page, count: $records)->all()->present('admin');

         $this->paginator = $this->data->paginator;
     }
}