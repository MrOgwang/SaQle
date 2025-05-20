<?php
declare(strict_types=1);

namespace SaQle\Core\Collection\Interfaces;

use ArrayIterator;
use IteratorAggregate;
use Traversable;
use Countable;
use ArrayAccess;

abstract class ICollection implements IteratorAggregate, ArrayAccess, Countable{
    
    private int $position;
    protected array $elements;

    public function __construct(array $elements){
        $this->elements = $elements;
    }

    public static function createEmpty(): static{
         return new static([]);
    }

    public static function fromMap(array $items, callable $fn): static{
        return new static(array_map($fn, $items));
    }

    public function reduce(callable $fn, mixed $initial): mixed{
        return array_reduce($this->elements, $fn, $initial);
    }

    public function map(callable $fn): array{
        return array_map($fn, $this->elements);
    }

    public function each(callable $fn): void{
        array_walk($this->elements, $fn);
    }

    public function some(callable $fn): bool{
        foreach ($this->elements as $index => $element) {
            if ($fn($element, $index, $this->elements)) {
                return true;
            }
        }

        return false;
    }

    public function filter(callable $fn): static{
        return new static(array_filter($this->elements, $fn, ARRAY_FILTER_USE_BOTH));
    }

    public function first(): mixed{
        return reset($this->elements);
    }

    public function last(): mixed{
        return end($this->elements);
    }

    public function merge(){
        
    }

    /*public function count(): int{
        return count($this->elements);
    }*/

    public function isEmpty(): bool{
        return empty($this->elements);
    }

    public function add(mixed $element): void{
        $this->elements[] = $element;
    }

    public function values(): array{
        return array_values($this->elements);
    }

    public function items(): array{
        return $this->elements;
    }

    public function getIterator(): Traversable{
        return new ArrayIterator($this->elements);
    }

     /**
     * Implementation of method declared in \Countable.
     * Provides support for count()
     */
    public function count() : int{
        return count($this->elements);
    }

    /**
     * Implementation of method declared in \ArrayAccess
     * Used to be able to use functions like isset()
     */
    public function offsetExists(mixed $offset) : bool{
        return isset($this->elements[$offset]);
    }

    /**
     * Implementation of method declared in \ArrayAccess
     * Used for direct access array-like ($collection[$offset]);
     */
    public function offsetGet(mixed $offset) : mixed{
        return $this->elements[$offset] ?? null;
    }

    /**
     * Implementation of method declared in \ArrayAccess
     * Used for direct setting of values
     */
    public function offsetSet(mixed $offset, mixed $value) : void{
        if (empty($offset)) { //this happens when you do $collection[] = 1;
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * Implementation of method declared in \ArrayAccess
     * Used for unset()
     */
    public function offsetUnset(mixed $offset) : void{
        unset($this->elements[$offset]);
    }
}
