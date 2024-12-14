<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, BigIntegerField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

class VercodeSchema extends TableSchema{
	 public IField $id;
	 public IField $code;
	 public IField $code_type;
	 public IField $email;
	 public IField $date_expires;

	 public function __construct(...$kwargs){
		$this->id           = new Pk(type: PRIMARY_KEY_TYPE);
		$this->code         = new TinyTextField(required: true, length: 100);
		$this->code_type    = new TinyTextField(required: true, length: 50);
		$this->email        = new TinyTextField(required: true, length: 200);
		$this->date_expires = new BigIntegerField(required: true, absolute: true, zero: false);
		
		parent::__construct(...$kwargs);
	 }
}
?>