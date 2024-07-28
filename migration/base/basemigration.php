<?php
namespace SaQle\Migration\Base;

use SaQle\Migration\Interfaces\IMigration;

abstract class BaseMigration implements IMigration{
     public function touched_contexts(){
     	return [];
     }
}
