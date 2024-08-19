<?php
namespace SaQle\Commons;
trait MoneyUtils{
	 static public function format_money($amount = 10000.457){
		 if(is_numeric($amount)){
			 $amount = round($amount, 2);
			 $amount_array = explode(".", $amount);
			 if(count($amount_array) == 1){
				 array_push($amount_array, "00");
			 }
			 if(count($amount_array) == 2){
			     $right = $amount_array[1];
			     $count = strlen($right);
			     if($count == 1){
				     $right = $right ."0";
			     }
			     unset($amount_array[count($amount_array)-1]);
			     $array = array_values($amount_array);
			     array_push($amount_array, $right);
			 }
             $string = join('.', $amount_array);
			 $string = self::format_shillings($string);
			 return $string;
	     }
	 }
	 static function format_shillings($amount, $currency = "KSH"){
         $formatted = $currency.".&nbsp;" . number_format(sprintf('%0.2f', preg_replace("/[^0-9.]/", "", $amount)), 2, ".", "&nbsp;");
         return $amount < 0 ? "({$formatted})" : "{$formatted}";
     }
}
?>