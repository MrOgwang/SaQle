<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use SaQle\Orm\Entities\Model\Manager\CreateManager;
use InvalidArgumentException;
use JsonSerializable;

abstract class ModelCollection extends TypedCollection implements IModel, JsonSerializable {

     private ?string $connection = null;

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
         self::assert_valid_data($data);
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
}
