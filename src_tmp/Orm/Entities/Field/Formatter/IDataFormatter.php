<?php
namespace SaQle\Orm\Entities\Field\Formatter;
abstract class IDataFormatter{
	abstract public function format($value);
	private function get_formatted_field_value($field, $value){
		 $formatted_data = $this->_format(array($field), array($value));
		 for($c = 0; $c < count($formatted_data['fields']); $c++){
			 if($formatted_data['fields'][$c] == $field){
				 $value = $formatted_data['values'][$c];
				 break;
			 }
		 }
		 return $value;
	 }
}
