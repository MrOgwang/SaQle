<?php
declare(strict_types=1);

namespace SaQle\Session\Models\Collections;

use SaQle\Session\Models\Session;
use SaQle\Dao\Model\Interfaces\ModelCollection;

final class SessionCollection extends ModelCollection{
	protected function type(): string{
		return Session::class;
	}
}
?>
