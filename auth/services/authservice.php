<?php
namespace SaQle\Auth\Services;

use SaQle\Http\Request\Request;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Auth\Services\Interface\IAuthService;
use SaQle\Auth\Models\Login;

abstract class AuthService implements IAuthService, Observable{
	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 protected $request;
	 public function __construct(){
	 	 $this->request = Request::init();
		 $this->__coConstruct();
	 }
     abstract public function authenticate() : array;
     abstract public function update_online_status(string | int $user_id, bool $is_online = true) : void;
	 public function record_signin(string | int $user_id){
		 $count = Login::db()->where('user_id__eq', $user_id)->total();
		 Login::db()->add([
		 	'login_count' => $count + 1, 
		 	'login_datetime' => time(), 
		 	'user_id' => $user_id,
		 	'logout_datetime' => 1,
		 	'login_span' => 1
		 ])->save();
	 }
	 public function record_signout(string | int $user_id) : void{
		 $last_login = Login::db()->where('user_id__eq', $user_id)->order(["login_id"], "DESC")->limit(1, 1)->first_or_default();
		 if($last_login){
		 	 $logout_datetime = time();
		 	 $login_span = $logout_datetime - $last_login->login_datetime;
			 Login::db()->where('login_id__eq', $last_login->login_id)->set(['logout_datetime' => time(), 'login_span' => $login_span])->update();
		 }
	 }
	 public function signout() : array{
	 	 //session_start();
	 	 session_unset();
         session_destroy();
         $this->feedback->set(FeedBack::SUCCESS, $this->request->user);
		 $this->notify();
		 return $this->feedback->get_feedback();
	 }
}
?>