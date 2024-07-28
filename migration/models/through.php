<?php
namespace SaQle\Migration\Models;

use SaQle\Dao\Field\Types\{Pk, OneToOne};
use SaQle\Dao\Field\Interfaces\IField;
use SaQle\Dao\Model\Dao;

class CategoryProduct extends Dao{
	public IField $id;
	public IField $category;
	public IField $product;

	public function __construct(){
		 $this->id = new Pk(type: 'GUID');
		 $this->category = new TextField(required: true, strict: false);
		 $this->migration_timestamp = new BigIntegerField(required: true, absolute: true, zero: false);
		 $this->is_migrated = new BooleanField(required: true, absolute: true, zero: true);
		 parent::__construct();

		 $this->set_meta([
   	 	     'auto_cmdt_fields' => true
         ]);
	}
}
?>