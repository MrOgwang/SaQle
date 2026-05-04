<?php
/**
 * A route object
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Components\ComponentDefinition;
use SaQle\Http\Request\RequestScope;
use SaQle\Http\Response\ResponseType;
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
         public ComponentDefinition $compiled_target,

         //the route name
         public string $name, 

         //the route scope
         public RequestScope $scope, 

         //the response types.
         public ?ResponseType $restype = null,

         //the route handler
         public ?string $model_class = null,

         //the layout wrappers
         public ?array $layout = null,

         //permissions, roles and attributes to enforce on route
         public ?array $guards = null,

         //the prefix the url came with
         public ?string $prefix = null,

         //the sse meta data
         public ?array $sse = null,
     ){}

     //check if the matched route supports a given response type
     public function supports(string $response_type){
         return in_array($response_type, $this->restype, true);
     }
}
