<?php
namespace SaQle\Dao\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;

class EagerLoadAssign extends BaseHandler{

     public function handle(mixed $row): mixed{

         $data = $this->params['data'];
         foreach($data as $include_field => $include_info){
            $row->$include_field = $include_info['multiple'] ? [] : null;
            $primary_key_name  = $include_info['key'];
            $primary_key_value = $row->$primary_key_name;

            if(array_key_exists($primary_key_value, $include_info['data'])){
                 $row->$include_field = $include_info['multiple'] ? $include_info['data'][$primary_key_value] 
                 : (count($include_info['data'][$primary_key_value]) > 0 ? $include_info['data'][$primary_key_value][0] : null);
            }elseif(isset($include_info['collection_class']) && $include_info['multiple']){
                $collection_class = $include_info['collection_class'];
                $row->$include_field = new $collection_class([]);
            }
         }

         return parent::handle($row);
     }
}

?>