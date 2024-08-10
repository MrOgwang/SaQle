<?php
namespace SaQle\Dao\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;
use SaQle\Commons\DateUtils;

class FormatCmdt extends BaseHandler{
     use DateUtils;

     public function handle(mixed $row): mixed{
         $cat_name = $this->params['cat_name'];
         $mat_name = $this->params['mat_name'];

         if(isset($row->$cat_name)){
             $new_p1 = $cat_name."_display";
             $new_p2 = $cat_name."_display2";
             $row->$new_p1 = self::format_date($row->$cat_name, DATE_ADDED_FORMAT);
             $row->$new_p2 = self::format_date($row->$cat_name, DATETIME_DISPLAY_FORMAT);
         }
         if(isset($row->$mat_name)){
             $new_p1 = $mat_name."_display";
             $new_p2 = $mat_name."_display2";
             $row->$new_p1 = self::format_date($row->$mat_name, DATE_ADDED_FORMAT);
             $row->$new_p2 = self::format_date($row->$mat_name, DATETIME_DISPLAY_FORMAT);
         }

         return parent::handle($row);
     }

}

?>