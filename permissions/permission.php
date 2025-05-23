<?php
namespace SaQle\Permissions;

use SaQle\Commons\UrlUtils;

abstract class Permission{
	 use UrlUtils;
	 protected $safe_methods = ['GET', 'HEAD', 'OPTIONS'];
	 protected $redirect_url = ACCESS_DENIED_REDIRECT_URL ? ACCESS_DENIED_REDIRECT_URL : '';
	 protected $request_url  = "";
	 protected $setup_info   = null;
	 protected $request      = null;
	 public function __construct(...$setup_info){
	 	 $this->request      = resolve('request');
		 $this->request_url  = self::get_full_url();
		 $this->setup_info   = $setup_info;
	 }
	 
	 public abstract function has_permission() : bool;

	 public function get_redirect_url(){
	 	return $this->redirect_url;
	 }

	 public function get_request_url(){
	 	return $this->request_url;
	 }
}
