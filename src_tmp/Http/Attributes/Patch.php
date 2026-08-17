<?php

namespace SaQle\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Patch extends HttpMethod {

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
             method:     "PATCH",
             authorize:  $authorize,
             layout:     $layout,
             model:      $model,
             middleware: $middleware
         );
     }
}