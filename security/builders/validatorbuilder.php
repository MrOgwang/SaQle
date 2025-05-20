<?php
namespace SaQle\Security\Builders;

use SaQle\Security\Validators\{IValidator, InputValidator, NullValidator, TypeValidator, ChoicesValidator, EmptyValidator, LengthValidator, MaxValidator, MinValidator, PatternValidator, StrictValidator, UploadValidator, ZeroValidator, SpecialValidator, AbsoluteValidator};

class ValidatorBuilder{
	private IValidator $validator;
	private            $configurations;
	public function __construct(...$configurations){
		$this->validator      = new InputValidator();
		$this->configurations = $configurations;
	}

	public function null() : ValidatorBuilder{
		$this->validator = new NullValidator($this->validator);
		return $this;
	}

	public function type() : ValidatorBuilder{
		$this->validator = new TypeValidator($this->validator);
		return $this;
	}

	public function choices() : ValidatorBuilder{
		$this->validator = new ChoicesValidator($this->validator);
		return $this;
	}

	public function empty(){
		$this->validator = new EmptyValidator($this->validator);
		return $this;
	}

	public function length(){
		$this->validator = new LengthValidator($this->validator);
		return $this;
	}

	public function max(){
		$this->validator = new MaxValidator($this->validator);
		return $this;
	}

	public function min(){
		$this->validator = new MinValidator($this->validator);
		return $this;
	}

	public function pattern(){
		$this->validator = new PatternValidator($this->validator);
		return $this;
	}

	public function strict(){
		$this->validator = new StrictValidator($this->validator);
		return $this;
	}

	public function upload(){
		$this->validator = new UploadValidator($this->validator);
		return $this;
	}

	public function zero(){
		$this->validator = new ZeroValidator($this->validator);
		return $this;
	}

	public function special(){
		$this->validator = new SpecialValidator($this->validator);
		return $this;
	}

	public function absolute(){
		$this->validator = new AbsoluteValidator($this->validator);
		return $this;
	}

	public function build() : IValidator{
		$validator       = $this->validator;
		$this->validator = new InputValidator();
		return $validator;
	}
}

