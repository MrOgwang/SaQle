<?php
namespace SaQle\Auth\Models\Schema;

use SaQle\Dao\Field\Types\{Pk, TextField, IntegerField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Schema\TableSchema;

class BaseTenantSchema extends TableSchema{
	public IField $tenant_id;
	public IField $tenant_name;
	public IField $users;

	public function __construct(...$kwargs){
		$this->tenant_id = new Pk(type: PRIMARY_KEY_TYPE);
		$this->tenant_name = new TextField(required: true, strict: false);
		$this->users = new ManyToMany(fdao: AUTH_MODEL_CLASS, pk: 'tenant_id', fk: 'tenant_id');
		parent::__construct(...$kwargs);
	}
}
?>