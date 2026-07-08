<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

use SaQle\Core\Collection\Base\TypedCollection;
use SaQle\Orm\Entities\Model\Interfaces\IModel;
use SaQle\Orm\Entities\Model\Schema\Model;

class GenericModelCollection extends ModelCollection {
     private string $type;

     public function __construct(string $type, array $elements = []){
         $this->type = $type;
         parent::__construct($elements);
     }

     protected function type(): string {
         return $this->type;
     }
}
