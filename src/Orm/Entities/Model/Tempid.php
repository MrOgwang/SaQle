<?php
namespace SaQle\Orm\Entities\Model;

use SaQle\Orm\Entities\Model\Schema\TempModel;
use SaQle\Orm\Entities\Field\Types\{Pk, CharField, IntegerField};
use SaQle\Orm\Entities\Model\Schema\Table;

class TempId extends TempModel {
	 protected function table_schema(Table $table) : void {

	 	 $table->fields([
		     'id_value' => strtolower(config('model.pk_type')) === 'auto' ? 
		      Table::integer_field()->required()->unsigned()->min(1) : 
		      Table::char_field()->required()
	 	 ]);

	 	 $table->with_user_audit(false);
		 $table->with_timestamps(false);
		 $table->with_soft_delete(false);
		 $table->temporary(true);
	 }
}
