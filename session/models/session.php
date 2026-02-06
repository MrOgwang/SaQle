<?php
namespace SaQle\Session\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, CharField, TextField};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Session extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'id'            => new Pk(),
		     'session_id'    => new CharField(required: true, length: 100),
		     'session_data'  => new TextField()
	 	 ];

	 	 $meta->with_soft_delete  = false;
	 	 $meta->with_user_audit   = false;
	 	 $meta->with_timestamps   = false;
	 }
}
