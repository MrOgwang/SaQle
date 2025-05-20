<?php
namespace SaQle\Backoffice;

class NavModel{
	public function __construct(
		private string $singular_name,
		private string $plural_name,
		private string $verbose_name,
		private string $schema
	){

	}
}

