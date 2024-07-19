<?php

namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Scalar;
use SaQle\Dao\Field\Interfaces\IField;

class Pk extends Scalar implements IField{
	public function __construct(string $type, ...$kwargs){
		parent::__construct(...$kwargs);
	}
}
?>