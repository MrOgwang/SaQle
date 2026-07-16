<?php
namespace SaQle\Commons;

final class Url {

	 public static function add_query($url, $param_name, $param_value, bool $append = false){
         
         $param_name = is_array($param_name) ? $param_name : [$param_name];

		 $param_value = is_array($param_value) ? $param_value : [$param_value];

		 for($p = 0; $p < count($param_name); $p++){
			 $url = self::build(self::data($url, $param_name[$p], $param_value[$p], $append));
		 }

         return $url;
     }

     public static function get_query(string $param_name, mixed $default = '', ?string $url = null){
         
         $url = $url ?? self::get_full();

         $url_components = parse_url($url);

         if(!array_key_exists('query', $url_components)){
             return $default;
         }

         parse_str($url_components['query'], $params);

         return $params[$param_name] ?? $default;
     }

	 private static function data($url, $param_name, $param_value, bool $append = false){

		 $data = parse_url($url);

         if(!isset($data["query"])){
             $data["query"] = "";
         }
             
         $params = [];
         parse_str($data['query'], $params);

         if($append){
             if(isset($params[$param_name])){
                 if(!is_array($params[$param_name])){
                     $params[$param_name] = [$params[$param_name]];
                 }

                 $params[$param_name][] = $param_value;
             }else{
                 $params[$param_name] = [$param_value];
             }
         }else{
             $params[$param_name] = $param_value;
         }

         $data['query'] = http_build_query($params);

		 return $data;
	 }

	 public static function build($data){
         $url = "";
         if(isset($data['host'])){
             $url .= $data['scheme'] . '://';
             if(isset($data['user'])){
                 $url .= $data['user'];
                 if(isset($data['pass'])){
                     $url .= ':' . $data['pass'];
                 }
                 $url .= '@';
             }
             $url .= $data['host'];
             if(isset($data['port'])){
                 $url .= ':' . $data['port'];
             }
         }
		 if(isset($data['path'])){
             $url .= $data['path'];
         }
         if(isset($data['query'])){
             $url .= '?' . $data['query'];
         }
         if(isset($data['fragment'])){
             $url .= '#' . $data['fragment'];
         }
         return $url;
     }

	 public static function get_full($domain_only = false){

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

     public static function add_filters(string $url, array $filters) : string {

         foreach($filters as $filter){
             $url = self::add_query(
                 $url,
                 'filter',
                 implode(':', [
                     $filter['field'],
                     $filter['operator'],
                     $filter['value']
                 ]),
                 true
             );
         }

         return $url;
     }

     public static function get_filters(?string $url = null): array {
         if($url === null){
             $url = self::get_full();
         }

         $data = parse_url($url);

         if(!isset($data['query'])){
             return [];
         }

         parse_str($data['query'], $params);

         if(!isset($params['filter'])){
             return [];
         }

         $filters = $params['filter'];

         if(!is_array($filters)){
             $filters = [$filters];
         }

         $result = [];

         foreach($filters as $filter){

             $parts = explode(':', $filter, 3);

             if(count($parts) !== 3){
                 continue;
             }

             $result[] = [
                 'field'    => rawurldecode($parts[0]),
                 'operator' => rawurldecode($parts[1]),
                 'value'    => rawurldecode($parts[2])
             ];
         }

         return $result;
     }
}
