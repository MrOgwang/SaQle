<?php
declare(strict_types = 1);

namespace SaQle\Orm\Query\Helpers;

use SaQle\Orm\Query\Limit\{LimitBuilder, Limit};

trait LimitManager{
     /**
     * The limit query builder
     * */
     public protected(set) LimitBuilder $lbuilder {
         set(LimitBuilder $value){
             $this->lbuilder = $value;
         }

         get => $this->lbuilder;
     }

     public function __construct(){
         $this->lbuilder = new LimitBuilder();
     }

     /**
     * Limit the number of rows returned by a select query.
     * @param int $page - the page to fetch
     * @param int records - the number of records to fetch.
     */
     public function limit(int $page = 1, int $records = 10){
         $this->lbuilder->limit = new Limit(page: $page, records: $records);
         return $this;
     }

     protected function get_limit_records(){
         $limit = $this->lbuilder->limit;
         return $limit ? $limit->records : 0;     
     }
}
?>