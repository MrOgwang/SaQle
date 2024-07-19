<?php
declare(strict_types = 1);
namespace SaQle\Dao\Model\Attributes;

/**
* When a data access object class is decorated with this attribute, the data
* inserted into the database will be injected with additional columns as follows
* INT/STRING added_by:    to track the id of the user that added the row
* INT/STRING modified_by: to track the id of the user that modified the row
* 
* The values for these IDs will be picked from the current request object's user property 
* if the user is signed in.
*/

#[\Attribute(Attribute::TARGET_CLASS)]
class CreatorModifierFields{
	 
}
?>
