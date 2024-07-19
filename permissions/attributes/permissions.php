<?php
declare(strict_types = 0);
namespace SaQle\Permissions\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Permissions{
	 private array $permissions;
	 public function __construct(array $permissions){
	 	$this->permissions = $permissions;
	 }

	 public function get_permissions(){
	 	return $this->permissions;
	 }
}
?>