<?php

namespace SaQle\Core\Migration\Seed;

abstract class DbSeed{
	abstract public static function get_seeds() : array;
}

