<?php
namespace SaQle\Auth\Services;

use SaQle\Http\Request\Request;
use SaQle\Auth\Models\Login;
use SaQle\Auth\Services\Jwt;
use SaQle\Core\Services\IService;

abstract class AuthService implements IService{
	 protected $request;
	 public function __construct(){
	 	 $this->request = Request::init();
	 }
     abstract public function update_online_status(string | int $user_id, bool $is_online = true) : void;
     abstract public function authenticate(...$kwargs);
	 public function record_signin(string | int $user_id){
		 /*$count = Login::get()->where('user_id__eq', $user_id)->total();
		 Login::new([
		 	'login_count' => $count + 1, 
		 	'login_datetime' => time(), 
		 	'user_id' => $user_id,
		 	'logout_datetime' => 1,
		 	'login_span' => 1
		 ])->save();*/
	 }
	 public function record_signout(string | int $user_id) : void{
		 $last_login = Login::get()->where('user_id__eq', $user_id)->order(["login_id"], "DESC")->limit(1, 1)->first_or_default();
		 if($last_login){
		 	 $logout_datetime = time();
		 	 $login_span = $logout_datetime - $last_login->login_datetime;
			 Login::set(['logout_datetime' => time(), 'login_span' => $login_span])->where('login_id__eq', $last_login->login_id)->update();
		 }
	 }
	 public function signout(){
	 	 $user = $this->request->user;
	 	 session_unset();
         session_destroy();

         return $user;
	 }

	 /**
	  * Generate a new jwt auth token
	  * 
	  * @param int    $issued_at:  the time issued in secends
	  * @param string $issuer:     the domain issuing the token
	  * @param int    $not_before: the time in seconds before which token is not valid
	  * @param int    $expiry:        the time in minutes after which token is not valid
	  * @param array  $extra_info:    a key=>value array of extra information to pass as payload
	  * 
	  * @return string
	  * */
	 public function generate_jwt_token(
	 	 ?int    $issued_at  = null, 
	 	 ?string $issuer     = null, 
	 	 ?int    $not_before = null, 
	 	 int     $expiry     = 5, 
	 	 array   $extra_info  = []
	 ) : string {
	     $issuer = $issuer ?? ROOT_DOMAIN;
	     $issued_at = $issued_at ?? time();
	     $not_before = $not_before ?? time();
	 	 $payload = [
             'iat'       => $issued_at,
             'iss'       => $issuer,
             'nbf'       => $not_before,
             'exp'       => $issued_at + ($expiry * 60),
         ];
         $payload = array_merge($payload, $extra_info);
         $token = (new Jwt(JWT_KEY))->encode($payload);
         return $token;
	 }
}
?>