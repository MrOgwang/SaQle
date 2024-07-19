<?php
$GLOBALS["configurations"] = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/config/config.ini");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('session.cookie_domain', $GLOBALS["configurations"]['ROOT_DOMAIN']);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] ."/config/config.php";
require_once INCLUDES . "/includes.php";
require_once SECURITY . "/sec.php";
require_once FEEDBACK . "/feedback.php";
require_once OBSERVABLE ."/observable.php";
require_once PERMISSION ."/permissions.php";
require_once ACCOUNT_M . "/auth.php";
require_once ACCOUNT_M . "/accounts.php";
require_once SESSION . "/sessionhandler.php";
require_once COMMON."/commons.php";
require_once DIRMANAGER."/dirmanager.php";
use \SaQle\Auth as Authentication;
use \SaQle\Security as Security;
use \SaQle\Observable as Observable;
use \SaQle\FeedBack as FeedBack;
use \SaQle\Permissions as Permissions;
use \SaQle\Commons as Commons;
use \SaQle\DirManager as DirManager;
session_start();
abstract class Ajax implements Observable\Observable{
	 use Commons\Commons, Observable\ConcreteObservable{
		 Observable\ConcreteObservable::__construct as private __coConstruct;
	 }
	 protected $security;
	 protected $dir_manager;
	 protected $permission_classes = ['AllowAny'];
	 private $http_status_code = [
        200 => 'Success',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable – You requested a format that isn’t json',
        429 => 'Too Many Requests – You’re requesting too many kittens! Slow down!',
        500 => 'Internal Server Error – We had a problem with our server. Try again later.',
        503 => 'Service Unavailable – We’re temporarily offline for maintenance. Please try again later.'
     ];
	 private $response;
	 public function __construct(){
		 $this->security = new Security\Security();
		 $this->dir_manager = new DirManager\DirManager();
	 }
	 private function authenticate(){
		 if(isset($_GET['at'])){
			 $auth = new Authentication\NormalAuth();
		     $auth->ajax_authenticate($_GET['at']);
		 }
	 }
	 protected function send_json_headers(){
		 header("Expires: 0");
		 header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		 header("Cache-Control: no-store, no-cache, must-revalidate");
		 header("Cache-Control: post-check=0, pre-check=0", false);
		 header("Pragma: no-cache");
		 header("Content-Type: application/json; charset=utf-8");
	 }
	 protected function send_stream_headers(){
		 header("Cache-Control: public");
         header("Content-Description: File Transfer");
         header("Content-Disposition: attachment; filename=".$file."");
         header("Content-Transfer-Encoding: binary");
         header("Content-Type: binary/octet-stream");
	 }
	 protected function send_xml_headers(){
		 
	 }
	 protected function send_event_stream_headers(){
		 header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
	 }
	 protected function clean_up_data(array $expected, $origin = "form"){
		 $validation_feedback = $this->security->extract_input($expected);
		 if($validation_feedback["status"] !== 0){
			 $this->respond(400, "Some or all input fields failed validation!", $validation_feedback['feedback']['dirty']);
		 }
		 return $validation_feedback['feedback']['clean'];
	 }
	 protected function groom_dao($dao, $extracted, $defaults = null){
		 $filters = array_key_exists("filters", $extracted) && !is_null($extracted['filters']) ? json_decode($extracted["filters"]) : (!is_null($defaults) && array_key_exists("filters", $defaults) ? $defaults['filters'] : null);
		 $limit = array_key_exists("limit", $extracted) && !is_null($extracted['limit']) ? json_decode($extracted["limit"]) : (!is_null($defaults) && array_key_exists("limit", $defaults) ? $defaults['limit'] : null);
		 $order = array_key_exists("order", $extracted) && !is_null($extracted['order']) ? json_decode($extracted["order"]) : (!is_null($defaults) && array_key_exists("order", $defaults) ? $defaults['order'] : null);
		 $paginate = array_key_exists("paginate", $extracted) && !is_null($extracted['paginate']) ? json_decode($extracted["paginate"]) : (!is_null($defaults) && array_key_exists("paginate", $defaults) ? $defaults['paginate'] : null);
		 if(!is_null($filters)) $dao->filter($filters);
		 if(!is_null($limit)) $dao->limit($limit->records, $limit->page);
		 if(!is_null($order)) $dao->order($order->columns, $order->direction);
		 if(!is_null($paginate)) $dao->paginate($paginate);
		 return $dao;
	 }
	 private function feedbackstatus_to_statuscode($status){
		 $_http_status_code = 500;
		 switch($status){
			 case FeedBack\FeedBack::INVALID_INPUT:
			     $_http_status_code = 400;
			 break;
			 case FeedBack\FeedBack::DB_ERROR:
			     $_http_status_code = 404;
			 break;
			 case FeedBack\FeedBack::GENERAL_ERROR:
			     $_http_status_code = 400;
			 break;
			 case FeedBack\FeedBack::SUCCESS:
			     $_http_status_code = 200;
			 break;
		 }
		 return $_http_status_code;
	 }
	 public function respond($http_status_code = null, $message = null, $response = null, $feedback = null){
		 if($feedback){
			 $http_status_code = $this->feedbackstatus_to_statuscode($feedback['status']);
			 $message = $feedback['message'];
			 $response = $feedback['feedback'];
		 }
		 if(!$message)
			 $message = $this->http_status_code[$http_status_code];
         http_response_code($http_status_code);
		 if($http_status_code !== 200){
			 $this->response = [
				'HttpVerb' => $_SERVER['REQUEST_METHOD'],
				'HttpStatusCode' => ''.$http_status_code,
				'HttpStatusMessage' => $this->http_status_code[$http_status_code],
				'Message' => $message,
				'Response' => $response,
			 ];
		 }else{
			 $this->response = $response;
		 }
		 print json_encode($this->response);
         exit;
	 }
}

class MobileRequest extends Ajax{
	 public function __construct(){
		 parent::__construct();
	 }
}

class WebRequest extends Ajax{
	 public function __construct(){
		 parent::__construct();
		 $this->set_accept_headers();
	 }
	 private function set_accept_headers(){
	     header("Access-Control-Allow-Origin: *");
	     header("Access-Control-Allow-Headers: Request-Source");
	     $array_key_exists = array_key_exists("HTTP_REQUEST_SOURCE", $_SERVER);
	     if($array_key_exists){
	         if(!isset($_SERVER['HTTP_ORIGIN'])) exit; //this is not a cross domain request.
    		 $wildcard = FALSE; // Set $wildcard to TRUE if you do not plan to check or limit the domains
    		 $credentials = TRUE; // Set $credentials to TRUE if expects credential requests (Cookies, Authentication, SSL certificates)
    		 $allowed_origins = array(
    			 rtrim(ROOT_DOMAIN, "/"),
    			 rtrim(ADMIN_DOMAIN, "/"),
    			 "http://www.postbnd.local/",
    			 "http://www.postbnd.local",
    			 "http://postbnd.saqle.com/",
    			 "http://postbnd.saqle.com",
    			 "https://postbnd.saqle.com/",
    			 "https://postbnd.saqle.com"
    		 );
    		 if (!in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins) && !$wildcard) exit;
    		 $origin = $wildcard && !$credentials ? '*' : $_SERVER['HTTP_ORIGIN'];
    		 header("Access-Control-Allow-Origin: " . $origin);
    		 if ($credentials) header("Access-Control-Allow-Credentials: true");
    		 header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PATCH");
    		 header("Access-Control-Allow-Headers: Origin");
    		 header('P3P: CP="CAO PSA OUR"'); // Makes IE to support cookies
    		 // Handling the Preflight
    		 if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') exit;
	     }
	     $this->send_json_headers();
	 }
}

abstract class APIRequest{
     use Commons\Commons;
	 const MOBILE = 1;
	 const WEB = 2;
	 private $request;
	 public function __construct($type){
		 switch($type){
			 case self::MOBILE:
			     $this->request = new MobileRequest();
			 break;
			 case self::WEB:
			     $this->request = new WebRequest();
			 break;
		 }
	 }
	 protected function respond($http_status_code = null, $message = null, $response = null, $feedback = null){
		 $this->request->respond($http_status_code, $message, $response, $feedback);
	 }
	 abstract protected function init();
	 abstract public function listen();
}
?>