<?php
namespace SaQle\Orm\Entities\Model;

use SaQle\Orm\Entities\Model\Collection\ModelCollection;

class TempIdCollection extends ModelCollection {
	 protected function type(): string {
	 	 return TempId::class;
	 }
}
