<?php

namespace SaQle\Core\Support;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

class AttributeBag implements ArrayAccess, IteratorAggregate, Countable
{
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    /**
     * Set an attribute.
     */
    public function set(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get an attribute.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Determine whether an attribute exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Remove an attribute.
     */
    public function remove(string $key): static
    {
        unset($this->attributes[$key]);

        return $this;
    }

    /**
     * Get all attributes.
     */
    public function all(): array
    {
        return $this->attributes;
    }

    /**
     * Replace all attributes.
     */
    public function replace(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Merge attributes.
     */
    public function merge(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Remove everything.
     */
    public function clear(): static
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * ArrayAccess
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->remove($offset);
    }

    /**
     * IteratorAggregate
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Countable
     */
    public function count(): int
    {
        return count($this->attributes);
    }
}