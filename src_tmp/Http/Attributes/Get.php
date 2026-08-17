<?php

namespace SaQle\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends HttpMethod {

     public function __construct(
         string  $url,
     	 string  $name,
     	 string  $authorize = "",
         array   $middleware = [],
     	 array   $layout = [],
         ?string $model = null
     ){
         parent::__construct(
             url:        $url,
             name:       $name,
             method:     "GET",
             authorize:  $authorize,
             layout:     $layout,
             model:      $model,
             middleware: $middleware
         );
     }
}