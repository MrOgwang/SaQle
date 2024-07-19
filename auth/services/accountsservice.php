<?php
namespace SaQle\Auth\Services;

use SaQle\Http\Request\Request;
use SaQle\Observable\{Observable, ConcreteObservable};
use SaQle\FeedBack\FeedBack;
use SaQle\Commons\StringUtils;
use SaQle\Services\Container\ContainerService;
use SaQle\Services\Container\Cf;

abstract class AccountsService implements IAccountService, Observable{
	 use StringUtils, ConcreteObservable{
		 ConcreteObservable::__construct as private __coConstruct;
	 }
	 protected $context;
	 public function __construct(protected Request $request, $context){
	 	 $this->context = Cf::create(ContainerService::class)->createDbContext($context);
		 $this->__coConstruct();
	 }

	 /**
	  * Check whether a verification code exists and return it else return false
	  * @param string $code: The code for which to checj existance
	  * */
	 protected function code_exists(string $code){
	 	 return $this->context->verificationcodes->where('code__eq', $code)->first_or_default() ?? false;
	 }

	 /**
	  * Create a verification code
	  * @param integer len:       The length of code to create.
	  * @param bool base64_encode: Whether to encode the results into base64 or not - default false
	  * @param string characters: 
	  * */
	 public function create_code(
	 	 int    $len = 30, 
	 	 bool   $base64_encode = false, 
	 	 string $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
	 ){
		 $generated_code = $this->random_string2($len, $base64_encode, $characters);
		 $existing_code  = $this->code_exists($generated_code);
		 while($existing_code){
			 $generated_code = $this->random_string2($len, $base64_encode, $characters);
			 $existing_code  = $this->code_exists($generated_code);
		 }
         return $generated_code;
	 }

	 /**
	  * Generate user verification code and save to database
	  * @param string email: The email address to associate with verification code
	  * */
	 public function save_verification_code(string $email){
		 $generated_code = $this->create_code(len: 5, characters: "0123456789");
		 //code expires 24 hours from the time of creation.
		 $code = $this->context->verificationcodes->add([
		 	"date_expires" => time() + (24 * 60 * 60), 
		 	"email"        => $email, 
		 	"code_type"    => "verification", 
		 	"code"         => $generated_code
		 ])->save();
		 return $code ? $this->feedback->get(status: FeedBack::SUCCESS, feedback: $code) : 
		 $this->feedback->get(status: FeedBack::GENERAL_ERROR, message: 'Verification code creation failed! Please try again.');
	 }

	 /**
	  * Take a full name and separate it into first name, middle name, last name and other names
	  * @param string $full_name
	  * */
	 protected function destruct_names(string $full_name){
	 	 $names            = explode(" ", $full_name);
	 	 if(count($names) <= 1){
	 	 	 throw new \Exception("Please provide two or more names");
	 	 }

	 	 $considered_names = array_slice($names, 0, 3);
	 	 if(count($considered_names) === 2){
	 	 	$new_considered_names = [$considered_names[0], "", $considered_names[1]];
	 	 	$considered_names = $new_considered_names;
	 	 }
	 	 $other_names      = array_slice($names, 3);
	 	 $considered_names[] = implode(" ", $other_names);
	 	 return $considered_names;
	 }

	 /**
	  * Check that a give contact exists
	  * @param string $contact:               The contact to check existance
	  * @param nullable string $contact_type: The type of contact, usually email, phone or address
	  * @param nullable string $owner_type:   The contact owner type, usually tenant or user
	  * */
	 protected function contact_exists(string $contact, ?string $contact_type = null, ?string $owner_type = null){
	 	 $contact = $this->context->contacts
	 	 ->where('contact__eq', $contact);
	 	 if($contact_type){
	 	 	 $contact = $contact->where('contact_type__eq', $contact_type);
	 	 }
	 	 if($owner_type){
	 	 	 $contact = $contact->where('owner_type__eq', $owner_type);
	 	 }
		 return $contact->first_or_default();
	 }

	 public function generate_verification_code($email, $password, $confirm_password){
	 	 /**
	 	 * Check that the provided email address does not already exists
	 	 * */
	 	 $contact = $this->contact_exists($email, 'email');
		 if($contact){
		 	 return $this->feedback->get(status: FeedBack::INVALID_INPUT, message: "The email address provided is already existing on our systems!");
		 }

		 /**
		  * Check that the passwords provided match
		  * */
		 if($password != $confirm_password){
		 	return $this->feedback->get(status: FeedBack::INVALID_INPUT, message: "The passwords provided do not match");
		 }

		 /**
		  * Generate and save code to the database
		  * */
		 $feedback = $this->save_verification_code($email);
		 $this->notify();
		 
		 return $feedback;
	 }

	 public function confirm_verification_code($email, $code){
	 	 $saved_code = $this->context->verificationcodes->where('email__eq', $email)->last_or_default();
		 if(!$saved_code){
		 	 return $this->feedback->get(status: FeedBack::INVALID_INPUT, message: "Invalid code provided!");
		 }

		 if($saved_code->code != $code){
		 	return $this->feedback->get(status: FeedBack::INVALID_INPUT, message: "Invalid code provided");
		 }

		 return $this->feedback->get(status: FeedBack::SUCCESS, message: "Codes match!");
	 }
}
