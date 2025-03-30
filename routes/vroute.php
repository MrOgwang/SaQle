<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

class Vroute extends Route{
     public function __construct(string $target){
         parent::__construct(['POST', 'GET'], '', $target);
     }
}
?>