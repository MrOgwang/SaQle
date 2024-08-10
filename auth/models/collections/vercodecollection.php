<?php
declare(strict_types=1);

namespace SaQle\Auth\Models\Collections;

use SaQle\Auth\Models\Vercode;
use SaQle\Dao\Model\Interfaces\ModelCollection;

final class VercodeCollection extends ModelCollection{
	protected function type(): string{
		return Vercode::class;
	}
}
?>
