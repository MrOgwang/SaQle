<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class LongTextField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "LONGTEXT";
		 $kwargs['length']      = isset($kwargs['length']) ? $kwargs['length'] : 4000;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : 4000;
		 parent::__construct(...$kwargs);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'textarea',
	 	 ]);
	 }
}
