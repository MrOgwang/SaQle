<?php

namespace SaQle\App;

use SaQle\Core\Support\AppContext;

abstract class Kernel {
     protected function app(){
         return AppContext::get();
     }

     abstract public function process(mixed $options = null);
}
