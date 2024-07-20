<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Field\Attributes\PrimaryKey;

class Pk extends Scalar implements IField{
	private $type;
	public function __construct(string $type, ...$kwargs){
		$this->type = $type;
		$this->attributes[PrimaryKey::class] = ['type' => $type];
		parent::__construct(...$kwargs);
	}

	public function get_key_type(){
		return $this->type;
	}
}
?>