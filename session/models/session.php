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

	 	 $meta->soft_delete         = false;
	 	 $meta->auto_cm             = false;
	 	 $meta->auto_cmdt           = false;
	 	 $meta->enable_multitenancy = false;
	 }
}
