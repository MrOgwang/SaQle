<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TinyTextField, BigIntegerField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Vercode extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'id'           => new Pk(),
		     'code'         => new TinyTextField(required: true, length: 100),
		     'code_type'    => new TinyTextField(required: true, length: 50),
		     'email'        => new TinyTextField(required: true, length: 200),
		     'date_expires' => new BigIntegerField(required: true, absolute: true, zero: false)
	 	 ];
	 }
}
?>