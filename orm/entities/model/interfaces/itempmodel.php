<?php
namespace SaQle\Orm\Entities\Model\Interfaces;

interface ITempModel{
	 public static function drop() : bool;
	 public static function create() : bool;
}
?>