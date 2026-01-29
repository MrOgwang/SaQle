<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, CharField, IntegerField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Login extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'login_id'        => new Pk(),
			 'login_count'     => new IntegerField(required: true, absolute: true, zero: false),
			 'login_datetime'  => new IntegerField(size: 'big', required: true, absolute: true, zero: false),
			 'logout_datetime' => new IntegerField(size: 'big', required: false, absolute: true, zero: false),
			 'login_span'      => new IntegerField(required: false, absolute: true, zero: false),
			 'login_location'  => new CharField(required: false, length: 200),
			 'login_device'    => new CharField(required: false, length: 200),
			 'login_browser'   => new CharField(required: false, length: 200),
			 'user_id'         => new CharField(required: true, length: 100)
	 	 ];
	 }
}
