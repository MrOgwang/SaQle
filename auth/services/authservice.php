<?php
namespace SaQle\Auth\Services;

use SaQle\Http\Request\Request;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;
use SaQle\Auth\Services\Interface\IAuthService;

abstract class AuthService implements IAuthService, Observable{
	 use ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 protected $context;
	 public function __construct(protected Request $request, $context){
	 	 $this->context = Cf::create(ContainerService::class)->createDbContext($context);
		 $this->__coConstruct();
	 }
     abstract public function authenticate();
	 public function record_signin(string | int $user_id){
		 $count = $this->context->logins->where('user_id__eq', $user_id)->total();
		 $this->context->logins->add([
		 	'login_count' => $count + 1, 
		 	'login_datetime' => time(), 
		 	'user_id' => $user_id,
		 	'logout_datetime' => 1,
		 	'login_span' => 1
		 ])->save();
	 }
	 public function record_signout(string | int $user_id){
		 $last_login = $this->context->logins->where('user_id__eq', $user_id)->order(["login_id"], "DESC")->limit(1, 1)->first_or_default();
		 if($last_login){
			 $this->context->logins->where('login_id__eq', $last_login->login_id)->set(['logout_datetime' => time(), 'login_span' => 0])->update();
		 }
	 }
	 public function update_online_status(string | int $user_id){
		 $this->context->users->where('user_id__eq', $user_id)->set(['is_online' => 1])->update();
	 }
	 public function sign_out(){
		 (new Dao\User(login_status: 0))->filter(["user_id", $_SESSION['current_user']->user_id])->update(update_fields: ["login_status"], partial: true);
		 (new Dao\TenantUser(online: 0))->filter(["user_id", $_SESSION['current_user']->user_id])->update(update_fields: ["online"], partial: true);
         $this->feedback->set(FeedBack\FeedBack::SUCCESS, (Object)["user_id"=>$_SESSION['current_user']->user_id]);
		 session_unset();
         $this->notify();
         return $this->feedback->get_feedback();
	 }
}
?>