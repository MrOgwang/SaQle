<?php
namespace SaQle\Dao\Model\Schema;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, IntegerField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Interfaces\ITempModel;

class TempIdSchema extends TableSchema implements ITempModel{
	public IField $id;
	public IField $id_value;
	public function __construct(...$kwargs){
		$this->id = new Pk(type: PRIMARY_KEY_TYPE);
		$this->id_value = PRIMARY_KEY_TYPE === 'auto' ? new IntegerField(required: true, absolute: true, zero: false) : new TinyTextField(required: true, strict: false);
		$this->set_meta([
   	 	     'auto_cm_fields'   => false,
	 	 	 'auto_cmdt_fields' => false,
	 	 	 'soft_delete'      => false
         ]);
		parent::__construct(...$kwargs);
		$this->set_temporary(true);
	}
}
?>