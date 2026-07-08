<?php
namespace SaQle\Orm\Entities\Model\Interfaces;

interface ITempModel{
	 public static function drop_table() : bool;
	 public static function create_table() : bool;
}
