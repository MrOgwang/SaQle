<?php
namespace SaQle\Dao\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;
use SaQle\Commons\{UrlUtils, StringUtils};

class PathsToUrls extends BaseHandler{
     use UrlUtils, StringUtils;
      
     private function get_file_url($file_name, $model, $row, $config, $file_key, $folder_path){
     	$show_file = $config[$file_key]['show_file'] ?? "";
 		if($show_file && method_exists($model, $show_file)){
			 $show_file = $model->$show_file($row);
		}
		$folder_path = $this->encrypt($folder_path, $file_name);
		return $this->add_url_parameter($show_file, ['file', 'xyz'], [$file_name, $folder_path]);
     }

     public function handle(mixed $row): mixed{
     	 $config = $this->params['config'];
     	 $model  = $this->params['model'];
     	 foreach($config as $file_key => $file_config){
 	 		 //get the file path
     	      $folder_path = $config[$file_key]['path'] ?? "";
	           if($folder_path && method_exists($model, $folder_path)){
 				 $folder_path = $model->$folder_path($row);
 			 }

 			 if(isset($row->$file_key) && $row->$file_key){
 			 	$files = explode("~", $row->$file_key);
 			 	$urls = [];
 			 	if(HIDDEN_MEDIA_FOLDER){
 			 		if(count($files) > 1){
	 			 		foreach($files as $file_name){
	 			 			$urls[] = $this->get_file_url($file_name, $model, $row, $config, $file_key, $folder_path);
	 			 		}
	 			 	}else{
	 			 		$urls[] = $this->get_file_url($files[0], $model, $row, $config, $file_key, $folder_path);
	 			 	}
 			 	}else{
 			 		if(count($files) > 1){
	 			 		foreach($files as $file_name){
	 			 			$urls[] = str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path).$file_name;
	 			 		}
	 			 	}else{
	 			 		$urls[] = str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path).$files[0];
	 			 	}
 			 	}
 			 	$row->$file_key = count($urls) > 1 ? $urls : $urls[0];
 			 }else{
 			 	 $default = $config[$file_key]['default'] ?? "";
 			 	 if($default && method_exists($model, $default)){
 			 	 	 $row->$file_key = $model->$default($row);
 			     }
 			 }
     	 }

         return parent::handle($row);
     }
}

?>