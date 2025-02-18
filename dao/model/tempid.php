<?php
namespace SaQle\Dao\Model;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, IntegerField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Interfaces\ITempModel;
use SaQle\Dao\Model\Schema\{Model, TableInfo};

class TempId extends Model implements ITempModel{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'id' => new Pk(),
		     'id_value' => PRIMARY_KEY_TYPE === 'auto' ? 
		                   new IntegerField(required: true, absolute: true, zero: false) : 
		                   new TinyTextField(required: true, strict: false)
	 	 ];
	 	 $meta->auto_cm     = false;
	 	 $meta->auto_cmdt   = false;
	 	 $meta->soft_delete = false;
		 $meta->temporary   = true;
	 }
}
?>