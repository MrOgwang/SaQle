<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;
use SaQle\Auth\Models\Schema\PermissionSchema;

class BasePermissionSchema extends TableSchema{
	public IField $permission_id;
	public IField $permission_name;
	public IField $permission_description;
	public IField $roles;
	public IField $users;

	public function __construct(...$kwargs){
		$this->permission_id = new Pk(type: PRIMARY_KEY_TYPE);
		$this->permission_name = new TinyTextField(required: true, strict: false);
		$this->permission_description = new TextField(required: false, strict: false);
		$this->roles = new ManyToMany(fdao: ROLE_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id');
		$this->users = new ManyToMany(fdao: AUTH_MODEL_CLASS, pk: 'permission_id', fk: 'permission_id');
		parent::__construct(...$kwargs);
	}
}
?>