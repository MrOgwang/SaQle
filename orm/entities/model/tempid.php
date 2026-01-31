<?php
namespace SaQle\Orm\Entities\Model;

use SaQle\Orm\Entities\Model\Schema\TempModel;
use SaQle\Orm\Entities\Field\Types\{Pk, CharField, IntegerField};
use SaQle\Orm\Entities\Model\Schema\TableInfo;

class TempId extends TempModel {
	 protected function model_setup(TableInfo $meta) : void {
	 	 $meta->fields = [
	 	 	 'id' => new Pk(config('primary_key_type')),
		     'id_value' => strtolower(config('primary_key_type')) === 'auto' ? 
		                   new IntegerField(required: true, unsigned: true, min: 1) : 
		                   new CharField(required: true, strict: false)
	 	 ];
	 	 $meta->auto_cm     = false;
	 	 $meta->auto_cmdt   = false;
	 	 $meta->soft_delete = false;
		 $meta->temporary   = true;
	 }
}
