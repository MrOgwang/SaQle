<?php

namespace SaQle\Auth\Interfaces;

interface StrategyRegistryInterface {
     public function add(string $name, LoginStrategyInterface $strategy): void;

     public function get(string $name): ?LoginStrategyInterface;

     public function all(): array;
}