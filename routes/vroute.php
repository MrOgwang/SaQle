<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use Override;

class Vroute extends Route{
     public function __construct(string $target){
         parent::__construct('', $target);
     }
     
     #[Override]
     public function matches() : array {
         return [false, false];
     }
}
?>