<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Ui\UiComponentDefinition;
use InvalidArgumentException;

final class MatchedRoute {
     public function __construct(
         //the route url
         public string $url,

         //the non prefix path
         public string $path,

         //the http method to handle
         public string $method,

         //the route handler
         public UiComponentDefinition $compiled_target,

         //the route name
         public string $name, 

         //the route handler
         public ?string $model_class = null,

         //the layout wrappers
         public ?array $layout = null,
         
         //permissions, roles and attributes to enforce on route
         public ?array $guards = null,

         //the prefix the url came with
         public ?string $prefix = null,

         //the route middleware
         public ?array $middleware = null,

         //the sse meta data
         public ?array $sse = null
     ){}
}
