<?php
declare(strict_types = 1);
namespace SaQle\Dao\Model\Attributes;

/**
* When a data access object class is decorated with this attribute, the data
* inserted into the database will be injected with additional columns as follows
* bool deleted: true / false to track whether a row is deleted or not.
*/

#[\Attribute(Attribute::TARGET_CLASS)]
class SoftDeleteFields{
	 
}
?>
