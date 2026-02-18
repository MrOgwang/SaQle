<?php
namespace SaQle\Orm\Entities\Model;

use SaQle\Orm\Entities\Model\Schema\TempModel;
use SaQle\Orm\Entities\Field\Types\{Pk, CharField, IntegerField};
use SaQle\Orm\Entities\Model\Schema\Table;

class TempId extends TempModel {
	 protected function table_schema(Table $table) : void {
	 	 $table->fields([
	 	 	 'id' => new Pk(config('primary_key_type')),
		     'id_value' => strtolower(config('primary_key_type')) === 'auto' ? 
		                   new IntegerField(required: true, unsigned: true, min: 1) : 
		                   new CharField(required: true)
	 	 ]);
	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
		 $table->temporary(true);
	 }
}
