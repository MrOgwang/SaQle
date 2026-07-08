<?php

namespace SaQle\Routes\Resources;

final class ResourceRouteDefinition {

     public const ROUTES = [
        'list'              => ['GET',    '/{resource}'],
        'read'              => ['GET',    '/{resource}/:id'],
        'create'            => ['POST',   '/{resource}'],
        'modify'            => ['PATCH',  '/{resource}/:id'],
        'remove'            => ['DELETE', '/{resource}/:id'],
        'truncate'          => ['DELETE', '/{resource}'],
        'relationships'     => ['GET', '/{resource}/:id/relationships/:rel'],
        'related'           => ['GET', '/{resource}/:id/:rel'],
     ];
}
