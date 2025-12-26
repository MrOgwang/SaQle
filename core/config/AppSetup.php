<?php

namespace SaQle\Core\Config;

final class AppSetup{
     public function __construct(
         public string $environment = 'development',
         public array $providers = [],
         public array $middlewares = [],
         public $environment_loader = null,
         public ?string $config_dir = null,
         public array $cors = [],
     ){}
}
