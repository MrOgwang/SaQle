<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TinyTextField, IntegerField, BigIntegerField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

class LoginSchema extends TableSchema{
	 public IField $login_id;
	 public IField $login_count;
	 public IField $login_datetime;
	 public IField $logout_datetime;
	 public IField $login_span;
	 public IField $login_location;
	 public IField $login_device;
	 public IField $login_browser;
	 public IField $user_id;
	
	 public function __construct(...$kwargs){
	 	 $this->login_id       = new Pk(type: PRIMARY_KEY_TYPE);
		 $this->login_count    = new IntegerField(required: true, absolute: true, zero: false);
		 $this->login_datetime = new BigIntegerField(required: true, absolute: true, zero: false);
		 $this->login_datetime = new BigIntegerField(required: false, absolute: true, zero: false);
		 $this->login_span     = new IntegerField(required: false, absolute: true, zero: false);
		 $this->login_location = new TinyTextField(required: false, length: 200);
		 $this->login_device   = new TinyTextField(required: false, length: 200);
		 $this->login_browser  = new TinyTextField(required: false, length: 200);
		 $this->user_id        = new TinyTextField(required: true, length: 100);

	 	 parent::__construct(...$kwargs);
	 }
}
?>