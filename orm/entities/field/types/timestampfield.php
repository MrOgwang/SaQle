<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class TimestampField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type']    = "DATETIME";
		 $kwargs['length']         = 30;
		 $kwargs['maximum']        = 30;
		 $kwargs['db_auto_init']   = $kwargs['db_auto_init'] ?? config('db_auto_init_timestamp');
		 $kwargs['db_auto_update'] = $kwargs['db_auto_update'] ?? config('db_auto_update_timestamp');
		 parent::__construct(...$kwargs);
	 }
}
