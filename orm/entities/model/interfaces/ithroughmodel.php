<?php
namespace SaQle\Orm\Entities\Model\Interfaces;

interface IThroughModel{
	public static function get_related_models() : array;
}
?>