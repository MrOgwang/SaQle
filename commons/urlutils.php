<?php
namespace SaQle\Commons;
trait UrlUtils{
	 public static function add_url_parameter($url, $param_name, $param_value){
         $param_name = is_array($param_name) ? $param_name : [$param_name];
		 $param_value = is_array($param_value) ? $param_value : [$param_value];
		 for($p = 0; $p < count($param_name); $p++){
			 $url = self::build_url(self::url_data($url, $param_name[$p], $param_value[$p]));
		 }
         return $url;
     }
	 private static function url_data($url, $param_name, $param_value){
		 $url_data = parse_url($url);
         if(!isset($url_data["query"]))
             $url_data["query"] = "";
         $params = [];
         parse_str($url_data['query'], $params);
         $params[$param_name] = $param_value;   
         $url_data['query'] = http_build_query($params);
		 return $url_data;
	 }
	 public static function build_url($url_data){
         $url = "";
         if(isset($url_data['host'])){
             $url .= $url_data['scheme'] . '://';
             if(isset($url_data['user'])){
                 $url .= $url_data['user'];
                 if(isset($url_data['pass'])){
                     $url .= ':' . $url_data['pass'];
                 }
                 $url .= '@';
             }
             $url .= $url_data['host'];
             if(isset($url_data['port'])){
                 $url .= ':' . $url_data['port'];
             }
         }
		 if(isset($url_data['path'])){
             $url .= $url_data['path'];
         }
         if(isset($url_data['query'])){
             $url .= '?' . $url_data['query'];
         }
         if(isset($url_data['fragment'])){
             $url .= '#' . $url_data['fragment'];
         }
         return $url;
     }
	 public static function get_full_url($domain_only = false){
		 if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'){
			 $url = "https://";   
		 }else{
			 $url = "http://";  
		 }    
         $url .= $_SERVER['HTTP_HOST'];
		 if(!$domain_only){
			 $url.= $_SERVER['REQUEST_URI'];
		 }
         return $url;  
	 }
	 public static function get_query_param($param_name, $default = ''){
		 $url = self::get_full_url();
         $url_components = parse_url($url);
		 if(!array_key_exists('query', $url_components)) return $default;
         parse_str($url_components['query'], $params);
         return $params[$param_name] ?? $default;
	 }
}
?>