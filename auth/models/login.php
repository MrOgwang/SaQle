<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Field\Types\{Pk, TinyTextField, IntegerField, BigIntegerField};
use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Model\Schema\{Model, TableInfo};

class Login extends Model{
	 protected function model_setup(TableInfo $meta) : void{
	 	 $meta->fields = [
	 	 	 'login_id'        => new Pk(),
			 'login_count'     => new IntegerField(required: true, absolute: true, zero: false),
			 'login_datetime'  => new BigIntegerField(required: true, absolute: true, zero: false),
			 'logout_datetime' => new BigIntegerField(required: false, absolute: true, zero: false),
			 'login_span'      => new IntegerField(required: false, absolute: true, zero: false),
			 'login_location'  => new TinyTextField(required: false, length: 200),
			 'login_device'    => new TinyTextField(required: false, length: 200),
			 'login_browser'   => new TinyTextField(required: false, length: 200),
			 'user_id'         => new TinyTextField(required: true, length: 100)
	 	 ];
	 }
}
