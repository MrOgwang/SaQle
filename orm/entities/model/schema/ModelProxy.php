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

     public function get(): ReadManager {
        return new ReadManager($this->model_class, $this->connection);
     }

     public function update(): UpdateManager {
        return new UpdateManager($this->model_class, $this->connection);
     }

     public function delete(): DeleteManager {
        return new DeleteManager($this->model_class, $this->connection);
     }
}
