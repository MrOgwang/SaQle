<?php
namespace SaQle\Controllers\Interfaces;

interface WebController{
	public function get_default() : string;
	public function get_block(): string;
}
