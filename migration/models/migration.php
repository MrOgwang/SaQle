<?php
namespace SaQle\Migration\Models;

use SaQle\Dao\Field\Types\{Pk, TextField, BigIntegerField, BooleanField, FileField, OneToOne, OneToMany, ManyToMany};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Dao;

class Migration extends Dao{
	public IField $migration_id;
	public IField $migration_name;
	public IField $migration_timestamp;
	public IField $is_migrated;

	public function __construct(){
		 $this->migration_id = new Pk(type: 'GUID');
		 $this->migration_name = new TextField(required: true, strict: false);
		 $this->migration_timestamp = new BigIntegerField(required: true, absolute: true, zero: false);
		 $this->is_migrated = new BooleanField(required: true, absolute: true, zero: true);

		 $this->set_meta([
   	 	     'auto_cm_fields'   => false,
	 	 	 'auto_cmdt_fields' => false,
	 	 	 'soft_delete'      => false
         ]);
         
		 parent::__construct();
	}
}
?>