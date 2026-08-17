<?php

namespace SaQle\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Post extends HttpMethod {

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
             method:     "POST",
             authorize:  $authorize,
             layout:     $layout,
             model:      $model,
             middleware: $middleware
         );
     }
}