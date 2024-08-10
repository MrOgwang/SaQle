<?php
declare(strict_types=1);

namespace SaQle\Core\Collection\Base;

use SaQle\Core\Assert\Assert;
use SaQle\Core\Collection\Interfaces\ICollection;

abstract class TypedCollection extends ICollection{
    public function __construct(array $elements = []){
         Assert::all_is_instance_of($elements, $this->type());
         parent::__construct($elements);
    }

    abstract protected function type(): string;

    public function add(mixed $element): void{
        Assert::is_instance_of($element, $this->type());
        parent::add($element);
    }
}
?>