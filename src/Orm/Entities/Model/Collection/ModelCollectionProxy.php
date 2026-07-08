<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

use SaQle\Orm\Entities\Model\Manager\CreateManager;
use InvalidArgumentException;

class ModelCollectionProxy {
     public function __construct(
         protected ModelCollection $model_collection
     ){}

     //add new row(s) to database or batch create new instances
     public function create(array $data) : CreateManager {

         ModelCollection::assert_valid_data($data);

         $this->model_collection->initialize_data($data);

         return new CreateManager($this->model_collection);
     }
}
