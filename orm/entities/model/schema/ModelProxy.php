<?php
namespace SaQle\Orm\Entities\Model\Schema;

use SaQle\Orm\Entities\Model\Manager\{CreateManager, UpdateManager, DeleteManager, TruncateManager, ReadManager, RunManager};

final class ModelProxy {
     public function __construct(
         protected Model $model_instance
     ){}

     public function new(array $data) : CreateManager {
         return new CreateManager($this->model_instance, $data);
     }

     public function get($tablealiase = null, $tableref = null): ReadManager {
         $readmanager = new ReadManager();
         $readmanager->initialize(model: $this->model_instance, tablealiase: $tablealiase, tableref: $tableref);
         return $readmanager;
     }

     public function set(array $data): UpdateManager {
         return new UpdateManager($this->model_instance, $data);
     }

     public function del(): DeleteManager {
         return new DeleteManager($this->model_instance);
     }

     public function empty(){
         return new TruncateManager($this->model_instance);
     }
}
