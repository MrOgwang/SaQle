<?php
namespace SaQle\Core\Migration\Base;

use SaQle\Core\Migration\Interfaces\IMigration;

abstract class BaseMigration implements IMigration{
     public function touched_contexts(){
     	return [];
     }
}
