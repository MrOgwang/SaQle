<?php
namespace SaQle\Http\Request\Processors;

use SaQle\Http\Response\HttpMessage;
use SaQle\Http\Response\Types\{JsonResponse, HtmlResponse};

class ApiRequestProcessor extends RequestProcessor {

	 private string $accept;

	 public function __construct(){
	 	 $this->accept = $_SERVER['HTTP_ACCEPT'] ?? 'application/json';
	 	  parent::__construct();
	 }

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
		 	 //[$target_classname, $target_method]   = $this->get_target_method($this->request->route->target, $this->request->route->action);
	 	     //[$http_message, $context_from_parent] = $this->get_target_response($target_classname, $target_method);
	 	     [$http_message, $context_from_parent] = $this->get_target_response($this->request->route->target, $this->request->route->action);
		 }
         $response_data    = $http_message->data;
         $http_status_code = (int)$http_message->code;
         http_response_code($http_status_code);
         if($http_status_code != 200){
             $response = [
                //'HttpVerb'          => $http_message->get_method(),
                'HttpStatusCode'    => $http_message->code,
                'HttpStatusMessage' => $http_message->status_message,
                'Message'           => $http_message->message,
                'Response'          => $response_data,
             ];
         }else{
             $response = $response_data;
         }
         print json_encode($response);

          
         /*if(strpos($this->accept, 'text/html') !== false){
         	 //return pre formatted html
             echo renderHtml($data);
         }else{
             //Default to JSON
             header('Content-Type: application/json');
             echo json_encode($data);
         }*/

         exit;
	 }
}
