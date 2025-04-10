<?php
namespace SaQle\Auth\Services;

use SaQle\Commons\StringUtils;
use SaQle\Auth\Models\{Vercode, Contact};
use SaQle\Core\Services\IService;

abstract class AccountsService implements IService{
	 use StringUtils;

	 /**
	  * Check whether a verification code exists and return it else return false
	  * @param string $code: The code for which to checj existance
	  * */
	 protected function code_exists(string $code){
	 	 return Vercode::get()->where('code__eq', $code)->first_or_default() ?? false;
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
	  * @param string contact: The email address or phone number to associate with verification code
	  * */
	 public function save_verification_code(string $contact){
		 $generated_code = $this->create_code(len: 5, characters: "0123456789");
		 //code expires 24 hours from the time of creation.
		 $code = Vercode::new([
		 	"date_expires" => time() + (24 * 60 * 60), 
		 	"contact"      => $contact, 
		 	"code_type"    => "verification", 
		 	"code"         => $generated_code
		 ])->save();

		 if(!$code)
		 	 internal_server_error_exception('Verification code creation failed! Please try again.');

		 return ok();
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
	 	 $contact = Contact::get()->where('contact__eq', $contact);
	 	 if($contact_type){
	 	 	 $contact = $contact->where('contact_type__eq', $contact_type);
	 	 }
	 	 if($owner_type){
	 	 	 $contact = $contact->where('owner_type__eq', $owner_type);
	 	 }
		 return $contact->first_or_default();
	 }

	 public function generate_verification_code($type, $contact, $password, $confirm_password){
	 	 //Check that the provided contact does not already exists
		 if($this->contact_exists($contact, $type))
		 	 bad_request_exception("The ".$type." you provided is already existing on our systems!");

		 //Check that the passwords provided match
		 if($password != $confirm_password)
		 	 bad_request_exception("The passwords provided do not match");

		 //Generate and save code to the database
		 return $this->save_verification_code($contact);
	 }

	 public function confirm_verification_code($contact, $code){
	 	 $saved_code = Vercode::get()->where('contact__eq', $contact)->last_or_default();
		 if(!$saved_code || ($saved_code && $saved_code->code != $code))
		 	 bad_request_exception("Invalid code provided!");

		 return ok();
	 }

	 public function confirm_username($username){
	 	 $usermodel = AUTH_MODEL_CLASS;
	 	 $user = $usermodel::get()->where('username__eq', $username)->first_or_default();
		 if($user)
		 	 bad_request_exception("This user name is taken!");

		 return ok();
	 }
}
