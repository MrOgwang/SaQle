<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Interfaces;

interface IHttpDataSourceManager{
	 public function get_value() : mixed;
	 public function is_valid() : bool;
}
