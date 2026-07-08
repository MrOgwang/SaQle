<?php
declare(strict_types = 1);
namespace SaQle\Orm\Entities\Model\Manager\Modes;

/**
 * This enumeration determinse what data is fecthed during a select operation
 * */
enum FetchMode : int {
	 /**
	  * Fetch only the non deleted rows
	  * */
	 case NON_DELETED = 0;
     
     /**
      * Fetch all rows. Deleted and non deleted included
      * */
	 case BOTH = 1;

	 /**
      * Fetch only the deleted rows
      * */
	 case DELETED = 2;
}
