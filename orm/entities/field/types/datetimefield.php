<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class DateTimeField extends FormattedField {
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "DATETIME";
		 $kwargs['length']      = 30;
		 $kwargs['maximum']     = 30;
		 parent::__construct(...$kwargs);
	 }

	 protected function get_validation_kwargs() : array{
		 return array_merge(parent::get_validation_kwargs(), ['format']);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'datetime-local',
	 	 ]);
	 }
}
