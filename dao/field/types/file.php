<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Binary;
use SaQle\Dao\Field\Interfaces\IField;

class File extends Binary implements IField{
	public function __construct(...$kwargs){
		parent::__construct(...$kwargs);
	}
}
?>