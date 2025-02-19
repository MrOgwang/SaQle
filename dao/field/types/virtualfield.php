<?php
namespace SaQle\Dao\Field\Types;

use SaQle\Dao\Field\Types\Base\Simple;

class VirtualField extends Simple{
	 public function get_validation_configurations() : array{
	 	 return [];
	 }

	 public function get_field_definition() : string | null{
	 	 return null;
	 }
}
?>