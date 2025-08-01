<?php
namespace SaQle\Commons;

trait Commons{
	 use DateUtils, FileUtils, MoneyUtils, PolygonUtils, StringUtils, UrlUtils;
	 public static function obj2array(&$instance){
         $clone = (array)$instance;
         $rtn = array();
         $rtn['___SOURCE_KEYS_'] = $clone;
         while(list($key, $value) = each($clone)){
             $aux = explode("\0", $key);
             $new_key = $aux[count($aux)-1];
             $rtn[$new_key] = &$rtn['___SOURCE_KEYS_'][$key];
         }
         return $rtn;
     }
	 public static function bless(&$instance, $class){
         if(!(is_array($instance))){
             return NULL;
         }
         //get source keys if available
		 if(isset($instance['___SOURCE_KEYS_'])){
			 $instance = $instance['___SOURCE_KEYS_'];
		 }
         //get serialization data from array
         $serdata = serialize($instance);
         list($array_params, $array_elems) = explode('{', $serdata, 2);
         list($array_tag, $array_count) = explode (':', $array_params, 3);
         $serdata = "O:".strlen ($Class).":\"$Class\":$array_count:{".$array_elems;
         $instance = unserialize($serdata);
         return $instance;
     }
	 static public function index_of(array $array, $element){
		 $array = array_merge($array);
		 $index = -1;
		 if(is_array($array)){
			 for($x = 0; $x < count($array); $x++){
				 if($array[$x] == $element){
					 $index = $x;
					 break;
				 }
			 }
		 }
		 return $index;
	 }
}
