<?php
namespace SaQle\Security\Validation\Managers;

use SaQle\Security\Validation\{NullValidator, TypeValidator, ChoicesValidator, EmptyValidator, LengthValidator, MaxValidator, MinValidator, PatternValidator, StrictValidator, UploadValidator, ZeroValidator, SpecialValidator, AbsoluteValidator, EmailValidator,PhoneValidator, UrlValidator, FormatValidator
 };
use SaQle\Security\Validation\Interfaces\IValidator;

class ValidatorManager {

	 private IValidator $validator;

	 public function __construct(string $rule_name){
	 	 $this->validator = match($rule_name){
	 	 	 'float'    => new FloatValidator(),
	 	 	 'string'   => new StringValidator(),
	 	 	 'int'      => new IntegerValidator(),
	 	 	 'accept'   => new FileTypeValidator(),
			 'null'     => new NullValidator(),
			 'choices'  => new ChoicesValidator(),
			 'empty'    => new EmptyValidator(),
			 'length'   => new LengthValidator(),
			 'maximum'  => new MaxValidator(),
			 'minimum'  => new MinValidator(),
			 'pattern'  => new PatternValidator(),
			 'strict'   => new StrictValidator(),
			 'zero'     => new ZeroValidator(),
			 'unsigned' => new AbsoluteValidator(),
			 'email'    => new EmailValidator(),
			 'phone'    => new PhoneValidator(),
			 'url'      => new UrlValidator(),
			 'format'   => new FormatValidator(),
	 	 };
	 }

	 public function get_validator() : IValidator {
	 	 return $this->validator;
	 }
}
