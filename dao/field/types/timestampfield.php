<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Interfaces\IField;

class TimestampField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']    = "DATETIME";
		 $kwargs['length']         = 30;
		 $kwargs['maximum']        = 30;
		 $kwargs['db_auto_init']   = $kwargs['db_auto_init'] ?? DB_AUTO_INIT_TIMESTAMP;
		 $kwargs['db_auto_update'] = $kwargs['db_auto_update'] ?? DB_AUTO_UPDATE_TIMESTAMP;
		 parent::__construct(...$kwargs);
	 }
}
?>