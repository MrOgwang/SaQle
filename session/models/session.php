<?php
namespace SaQle\Session\Models;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, TextField};
use SaQle\Dao\Model\Schema\{Model, TableInfo};

class Session extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'id'            => new Pk(),
		     'session_id'    => new TinyTextField(required: true, length: 100),
		     'session_data'  => new TextField(required: false, strict: false)
	 	 ];

	 	 $meta->soft_delete = false;
	 	 $meta->auto_cm     = false;
	 	 $meta->auto_cmdt   = false;
	 }
}
?>