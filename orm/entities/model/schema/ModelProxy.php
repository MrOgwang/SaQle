<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Model\Manager\{CreateManager, UpdateManager, DeleteManager, TruncateManager, ReadManager, RunManager};

final class ModelProxy {
     public function __construct(
         protected Model $model_instance
     ){}

     public function create(array $data) : CreateManager {
         $this->model_instance->initialize_model_data($data, false);
         return new CreateManager($this->model_instance);
     }

     public function get($tablealiase = null, $tableref = null): ReadManager {
         return new ReadManager($this->model_instance, $tablealiase, $tableref);
     }

     public function update(array $data): UpdateManager {
         return new UpdateManager($this->model_instance, $data);
     }

     public function delete(bool $permanently = false): DeleteManager {
         return new DeleteManager($this->model_instance, $permanently);
     }

     public function empty(){
         return new TruncateManager($this->model_instance);
     }

     public function run(string $sql, string $operation, ?array $data = null, bool $multiple = true){
         return new RunManager($this->model_instance, $sql, $operation, $data, $multiple);
     }
}
