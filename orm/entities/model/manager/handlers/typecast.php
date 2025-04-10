<?php
namespace SaQle\Orm\Entities\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;

class TypeCast extends BaseHandler{

     public function handle(mixed $row): mixed{
         $type = $this->params['type'];
         $params = (array)$row;
         unset($params['_sql_data_formatted']);
         $row = $type::from_db(...$params);
         return parent::handle($row);
     }
}

?>