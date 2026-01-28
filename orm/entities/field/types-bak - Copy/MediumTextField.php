<?php
namespace SaQle\Orm\Entities\Field\Types;

use SaQle\Orm\Entities\Field\Interfaces\IField;

class MediumTextField extends TextType implements IField{
	 public function __construct(...$kwargs){
		 $kwargs['column_type'] = "MEDIUMTEXT";
		 $kwargs['length']      = isset($kwargs['length']) ? $kwargs['length'] : 1000;
		 $kwargs['maximum']     = isset($kwargs['maximum']) ? $kwargs['maximum'] : 1000;
		 parent::__construct(...$kwargs);
	 }

	 public function get_control_kwargs() : array{
	 	 return array_merge(parent::get_control_kwargs(), [
	 	 	 'type' => 'textarea',
	 	 ]);
	 }
}
