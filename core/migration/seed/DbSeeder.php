<?php

namespace SaQle\Core\Migration\Seed;

abstract class DbSeeder {
	abstract public static function get_seeds() : array;
}

