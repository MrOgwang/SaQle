<?php
declare(strict_types=1);

namespace SaQle\Orm\Entities\Model\Collection;

class Paginator {
     public int $page;
     public int $per_page;
     public int $total_records;
     public int $total_pages;

     public function has_next(): bool{
         return $this->page < $this->total_pages;
     }

     public function has_prev(): bool{
         return $this->page > 1;
     }

     public function pages($window = 2){
         $start = max(1, $this->page - $window);
         $end = min($this->total_pages, $this->page + $window);
         return range($start, $end);
     }
}