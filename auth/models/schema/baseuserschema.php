<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TextField, OneToMany, ManyToMany};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

class BaseUserSchema extends TableSchema{
	public IField $user_id;
	public IField $first_name;
	public IField $last_name;
	public IField $username;
	public IField $password;
	public IField $label;

	/**
	 * Roles and permissions fields.
	 * */
	public IField $roles;
	public IField $permissions;

	/**
	 * Tenant field
	 * */
	public IField $tenants;

	public function __construct(...$kwargs){
		$this->user_id = new Pk(type: PRIMARY_KEY_TYPE);
		$this->first_name = new TextField(required: true, strict: false);
		$this->last_name = new TextField(required: true, strict: false);
		$this->username = new TextField(required: true, strict: false);
		$this->password = new TextField(required: true, strict: false);
		$this->label = new TextField(required: true, strict: true);

		if(ENABLE_RBAC){
			 $this->roles = new ManyToMany(fdao: ROLE_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
			 $this->permissions = new ManyToMany(fdao: PERMISSION_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
		}

		if(ENABLE_MULTITENANCY){
			 $this->tenants = new ManyToMany(fdao: TENANT_MODEL_CLASS, pk: 'user_id', fk: 'user_id');
		}

		parent::__construct(...$kwargs);
	}
}
?>