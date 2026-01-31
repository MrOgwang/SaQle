<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, CharField, IntegerField};
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Login extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'login_id'        => new Pk(),
			 'login_count'     => new IntegerField(required: true, unsigned: true, min: 1),
			 'login_datetime'  => new IntegerField(size: 'big', required: true, unsigned: true),
			 'logout_datetime' => new IntegerField(size: 'big', unsigned: true),
			 'login_span'      => new IntegerField(unsigned: true, min: 1),
			 'login_location'  => new CharField(length: 200),
			 'login_device'    => new CharField(length: 200),
			 'login_browser'   => new CharField(length: 200),
			 'user_id'         => new CharField(required: true, length: 100)
	 	 ];
	 }
}
