<?php
/**
* This is an auto generated file.
*
* The code here is designed to work as is, and must not be modified unless you know what you are doing.
*
* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough
* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup
* of coffee either.
*
* Commands to generate file:
* 1. php manage.php make:migrations
* 2. php manage.php make:collections
* On your terminal, cd into project root and run the above commands
* 
* A typed collection container for Contact
* */

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
