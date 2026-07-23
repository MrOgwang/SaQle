<?php

namespace SaQle\App;

use Closure;

final class HttpAppBuilder extends AppBuilder {

     public function cors(Closure $callback): self {

         $builder = new CorsBuilder();

         $callback($builder);

         $this->setup->cors = $builder->to_array();

         return $this;
     }

     public function middleware(Closure $callback) : self {

         $builder = new MiddlewareBuilder();

         $callback($builder);

         $this->setup->http_middleware = $builder;

         return $this;
     }

}