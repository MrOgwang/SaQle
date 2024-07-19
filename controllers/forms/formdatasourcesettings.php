<?php
namespace SaQle\Controllers\Forms;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FormDataSourceSettings{
	private array $from_form   = [];
	private array $from_sdata  = [];
	private array $pre_defined = [];
	public function __construct(array $from_form = [], array $from_sdata = [], array $pre_defined = []){
		$this->from_form   = $from_form;
		$this->from_sdata  = $from_sdata;
		$this->pre_defined = $pre_defined;
	}

	public function get_from_form(){
		return $this->from_form;
	}

	public function get_from_sdata(){
		return $this->from_sdata;
	}

	public function get_pre_defined(){
		return $this->pre_defined;
	}
}
?>