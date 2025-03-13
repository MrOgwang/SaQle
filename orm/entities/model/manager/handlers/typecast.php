<?php
namespace SaQle\Orm\Entities\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;

class TypeCast extends BaseHandler{

     public function handle(mixed $row): mixed{
         $type = $this->params['type'];
         $row = new $type(...(array)$row);

         return parent::handle($row);
     }
}

?>