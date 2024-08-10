<?php
namespace SaQle\Dao\Model\Manager\Handlers;

use SaQle\Core\Chain\Base\BaseHandler;
use SaQle\Commons\{UrlUtils, StringUtils};

class PathsToUrls extends BaseHandler{
     use UrlUtils, StringUtils;

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
 			 	if(HIDDEN_MEDIA_FOLDER){
 			 		$show_file = $config[$file_key]['show_file'] ?? "";
 			 		if($show_file && method_exists($model, $show_file)){
		 				 $show_file = $model->$show_file($row);
		 			}
		 			$folder_path = $this->encrypt($folder_path, $row->$file_key);
		 			$file_url = $this->add_url_parameter($show_file, ['file', 'xyz'], [$row->$file_key, $folder_path]);
 			 	}else{
 			 		$file_url = str_replace(DOCUMENT_ROOT, ROOT_DOMAIN, $folder_path).$row->$file_key;
 			 	}
 			 	$row->$file_key = $file_url;
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