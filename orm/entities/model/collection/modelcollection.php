<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use SaQle\Orm\Entities\Model\Manager\CreateManager;
use SaQle\Orm\Entities\Model\Schema\Model;
use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

abstract class ModelCollection extends TypedCollection implements IModel, JsonSerializable {

     private ?string $connection = null;

     public ?Paginator $paginator = null;

     public function __construct(array $elements = []){
         parent::__construct($elements);
     }

     abstract protected function type(): string;

     public function set_connection(string $connection){
         $this->connection = $connection;
     }

     public function get_connection(){
         return $this->connection;
     }

     public function set_paginator(Paginator $paginator){
         $this->paginator = $paginator;
     }

     public function get_paginator(){
         return $this->paginator;
     }

     public function initialize_data(array $elements = []){
         $type = $this->type();
        
         foreach($elements as $object){
             $model = new $type(...$object);
             $model->set_table_and_connection($this->connection);
             $this->add($model);
         }
     }

     //change the connection right before an operation
     public static function using(string $connection){
         $collection = new static();
         $collection->set_connection($connection);
         return new ModelCollectionProxy($collection);
     }

     //add new row(s) to database or batch create new instances
     public static function create(array $data) : CreateManager {
         $collection_class = get_called_class();
         if($collection_class == GenericModelCollection::class){
             throw new RuntimeException("Create cannot be called on a generic collection!");
         }

         self::assert_valid_data($data);

         foreach($data as $d){
             $d->set_table_and_connection();
         }

         return new CreateManager(new static($data));
     }

     static public function assert_valid_data(array $data): void {
         //Case 1: single object (associative array)
         if(is_assoc($data)){
             throw new InvalidArgumentException(
                 "Data is not well defined!"
             );
         }

         //Case 2: many objects (array of associative arrays)
         foreach($data as $item){
             if($item instanceof Model){
                 break;
             }

             $tmp_item = !is_array($item) ? (array)$item : $item;
             if(!is_assoc($tmp_item)){
                throw new InvalidArgumentException(
                    "The data to insert is not properly defined!"
                );
             }
         }

         //Empty array is ambiguous → reject
         if($data === []){
             throw new InvalidArgumentException(
                'Cannot insert empty data!'
             );
         }
     }

     public function get_data(){
         $data = [];
         foreach($this->elements as $el){
             $data[] = $el->get_data();
         }

         return $data;
     }

     public function get_insert_data(){
         $data = [];
         foreach($this->elements as $el){
             $data[] = $el->get_insert_data();
         }

         return $data;
     }

     public function get_file_references(){
         $files = [];
         foreach($this->elements as $el){
             $files[] = $el->get_file_references();
         }

         return $files;
     }

     public function get_files(){
         $files = [];
         foreach($this->elements as $el){
             $files[] = $el->get_files();
         }

         return $files;
     }

     final public function get_upload_session(){
         $sessions = [];
         foreach($this->elements as $el){
             $sessions[] = $el->get_upload_session();
         }

         return $sessions;
     }

     public function get_update_columns(){
         return $this->elements[0]->get_update_columns();
     }

     public function pluck(string $field, bool $filter_nulls = true) : array {
         $values = array_map(fn($model) => $model->$field ?? null, $this->elements);

         return $filter_nulls ? array_values(array_filter($values, fn ($v) => $v !== null)) : $values;
     }

     public function pluck_unique(string $field) : array {
         return array_values(array_unique($this->pluck($field)));
     }

     public function jsonSerialize() : mixed {
         return $this->items();
     }

     public function randomize() : static {
         shuffle($this->elements);
         return $this;
     }

     //pagination helper methods

     public function page(){
         return $this->paginator ? $this->paginator->page : 1;
     }

     public function per_page_count(){
         return $this->paginator ? $this->paginator->per_page : count($this->elements);
     }

     public function total_records(){
         return $this->paginator ? $this->paginator->total_records : count($this->elements);
     }

     public function total_pages(){
         return $this->paginator ? $this->paginator->total_pages : 1;
     }

     public function has_next(): bool {
         return $this->paginator ? $this->paginator->has_next() : false;
     }

     public function has_prev(): bool{
         return $this->paginator ? $this->paginator->has_prev() : false;
     }

     public function pages($window = 2){
         return $this->paginator ? $this->paginator->pages() : [1];
     }
}
