<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\PrimaryKey;

class Pk extends Scalar implements IField{
	public function __construct(string $type, ...$kwargs){
		$this->attributes[PrimaryKey::class] = ['type' => $type];
		parent::__construct(...$kwargs);
	}
}
?>