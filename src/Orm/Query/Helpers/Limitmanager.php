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
     * @param int records - the number of records to fetch.
     */
     public function limit(int $count){
         $this->before_limit();

         $this->lbuilder->limit = new Limit(page: 1, records: $count);

         $this->after_limit();

         return $this;
     }

     /**
     * Paginate the rows returned by a select query.
     * @param int $page - the page to fetch
     * @param int records - the number of records to fetch.
     */
     public function paginate(int $page, int $count){
         $this->paginate = true;

         $this->before_limit();

         $this->lbuilder->limit = new Limit(page: $page, records: $count);

         $this->after_limit();

         return $this;
     }

     public function get_limit_records(){
         $limit = $this->lbuilder->limit;
         return $limit ? $limit->records : 0;     
     }

     protected function before_limit(){

     }

     protected function after_limit(){
        
     }
}
