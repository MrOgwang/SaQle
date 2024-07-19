<?php
declare(strict_types = 1);
namespace SaQle\Dao\Model\Attributes;

/**
* When a data access object class is decorated with this attribute, the data
* inserted into the database will be injected with additional columns as follows
* BIGINT date_added:    integer datetimestamp for when a row is added
* BIGINT last_modified: integer datetimestamp for when a row was last modified
* 
*/

#[\Attribute(Attribute::TARGET_CLASS)]
class CreateModifyDateTimeFields{
	 
}
?>
