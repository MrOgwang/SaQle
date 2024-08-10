<?php
declare(strict_types=1);

namespace SaQle\Auth\Models\Collections;

use SaQle\Auth\Models\Login;
use SaQle\Dao\Model\Interfaces\ModelCollection;

final class LoginCollection extends ModelCollection{
	protected function type(): string{
		return Login::class;
	}
}
?>
