<?php

namespace SaQle\Core\Registries;

use SaQle\Auth\Interfaces\{
     StrategyRegistryInterface,
     LoginStrategyInterface
};

class LoginStrategyRegistry implements StrategyRegistryInterface {
     protected array $strategies = [];

     public function add(string $name, LoginStrategyInterface $strategy): void {
         $this->strategies[$name] = $strategy;
     }

     public function get(string $name): ?LoginStrategyInterface {
         return $this->strategies[$name] ?? null;
     }

     public function all(): array {
         return $this->strategies;
     }
}