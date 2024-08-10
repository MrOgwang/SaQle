<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, ManyToMany, TinyTextField, TextField};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;
use SaQle\Auth\Models\Schema\PermissionSchema;

class BaseRoleSchema extends TableSchema{
	public IField $role_id;
	public IField $role_name;
	public IField $role_description;
	public IField $permissions;
	public IField $users;

	public function __construct(...$kwargs){
		$this->role_id = new Pk(type: PRIMARY_KEY_TYPE);
		$this->role_name = new TinyTextField(required: true, strict: false);
		$this->role_description = new TextField(required: false, strict: false);
		$this->permissions = new ManyToMany(fdao: PERMISSION_MODEL_CLASS, pk: 'role_id', fk: 'role_id');
		$this->users = new ManyToMany(fdao: AUTH_MODEL_CLASS, pk: 'role_id', fk: 'role_id');
		parent::__construct(...$kwargs);
	}
}
?>