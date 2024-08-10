<?php
declare(strict_types=1);

namespace SaQle\Auth\Models\Collections;

use SaQle\Auth\Models\Contact;
use SaQle\Dao\Model\Interfaces\ModelCollection;

final class ContactCollection extends ModelCollection{
	protected function type(): string{
		return Contact::class;
	}
}
?>
