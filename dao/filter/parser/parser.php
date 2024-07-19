<?php
namespace SaQle\Dao\Filter\Parser;

use SaQle\Dao\Filter\Parser\Interfaces\IParser;

class Parser implements IParser{
	 public function parse_filters($group){
		 $parsed = array("clause" => "", "values" => []);
		 if($group && count($group->filters()) > 0){
			 $parsed["clause"] .= "(";
			 foreach($group->filters() as $g){
				 if(is_object($g)){
					 if($g->is_group()){
						 $pf = $this->parse_filters($g);
					     $parsed["clause"] .= $pf["clause"];
					     $parsed["values"] = array_merge($parsed["values"], $pf["values"]);
					 }else{
						 $filter_properties = $this->parse_basic_filter($g->filter(), $g->database(), $g->table());
						 $parsed["clause"] .= $filter_properties->field ." ".$filter_properties->operator." ".$filter_properties->placeholder;
					     if(is_array($filter_properties->value)){
							 $parsed["values"] = array_merge($parsed["values"], $filter_properties->value);
						 }else{
							 array_push($parsed["values"], $filter_properties->value);
						 }
					 }
				 }else{
					 $g = $g == "&" ? "AND" : "OR";
					 $parsed["clause"] .= " ".strtoupper($g)." ";
				 }
			 }
			 $parsed["clause"] .= ")";
		 }
	     return $parsed;
	 }

	 private function parse_basic_filter($filter, $database, $table){
		 $filter_object = ["placeholder" => "?"];
		 $first_index = strpos($filter, "~");
		 $secend_index = strpos($filter, "~", $first_index + 1);
		 $original_field = substr($filter, 0, $first_index);
		 $original_value = substr($filter, $secend_index + 1);
		 $filter_object["field"] = $original_field; //$this->translate($original_field, $table, $database);
		 $filter_object["operator"] = substr($filter, $first_index + 1, ($secend_index - $first_index - 1));
		 if($filter_object["operator"] == "IN"){
			 $original_value = str_replace(["(", ")"], "", $original_value);
			 $original_value_array = explode(", ", $original_value);
			 $filter_object ["placeholder"] = "(".str_repeat("?, ", count($original_value_array) - 1)."?)";
			 $filter_object["value"] = $original_value_array;
		 }else{
			 $filter_object["value"] = $original_value;
		 }
		 return (object)$filter_object;
	 }
	 
}
?>