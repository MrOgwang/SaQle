<?php
declare(strict_types=1);

namespace SaQle\Migration\Models\Collections;

use SaQle\Migration\Models\Migration;
use SaQle\Dao\Model\Interfaces\ModelCollection;

final class MigrationCollection extends ModelCollection{
	protected function type(): string{
		return Migration::class;
	}
}
?>
