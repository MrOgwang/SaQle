<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Http\Response\HttpMessage;

class ApiRequestProcessor extends RequestProcessor{

	 private function send_json_headers(){
		 header("Expires: 0");
		 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		 header("Cache-Control: no-store, no-cache, must-revalidate");
		 header("Cache-Control: post-check=0, pre-check=0", false);
		 header("Pragma: no-cache");
		 header("Content-Type: application/json; charset=utf-8");
	 }
	 private function send_stream_headers(){
		 header("Cache-Control: public");
         header("Content-Description: File Transfer");
         header("Content-Disposition: attachment; filename=".$file."");
         header("Content-Transfer-Encoding: binary");
         header("Content-Type: binary/octet-stream");
	 }
	 private function send_xml_headers(){
		 
	 }
	 private function send_event_stream_headers(){
		 header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
	 }
	 public function process(?HttpMessage $http_message = null){
		 $this->send_json_headers();
		 if(is_null($http_message)){
		 	 [$target_classname, $target_method] = $this->get_target_method($this->request->route->get_target());
	 	     $http_message     = $this->get_target_response($target_classname, $target_method);
		 }
         $response_data    = $http_message->get_response();
         $http_status_code = (int)$http_message->get_code();
         http_response_code($http_status_code);
         if($http_status_code != 200){
             $response = [
                'HttpVerb'          => $http_message->get_method(),
                'HttpStatusCode'    => $http_message->get_code(),
                'HttpStatusMessage' => $http_message->get_status_message(),
                'Message'           => $http_message->get_message(),
                'Response'          => $response_data,
             ];
         }else{
             $response = $response_data;
         }
         print json_encode($response);
         exit;
	 }
}
?>