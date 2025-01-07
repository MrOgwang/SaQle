<?php
namespace SaQle\Dao\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;

class FormattedChecker extends BaseHandler{

     public function handle(mixed $row): mixed{
         $row->_sql_data_formatted = true;
         return parent::handle($row);
     }

}

?>