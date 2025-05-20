<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * Define a relationship between two models.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

declare(strict_types = 1);
namespace SaQle\Orm\Entities\Field\Relations\Base;

use SaQle\Orm\Entities\Field\Relations\Interfaces\IRelation;
use SaQle\Orm\Entities\Field\Relations\Traits\Relationship;

class BaseRelation implements IRelation{
	 use Relationship;

	 /**
	  *  Create a one to one relation instance
	  * @param          string $pmodel:     The model class name for table A, where primary key is defined.
	  * @param          string $fmodel:     The model class name for table B, where foreign key is defined.
	  * @param nullable string $field:      The name of the field on table A to assign the table B object to on fetch
	  * @param nullable string $pk:         The field on table A to use as primary key, if not provided defaults to the primary key defined on A
	  * @param nullable string $fk:         The field on table B to use as foreign key, if not provided defaults to the primary key defined on B
	  * @param          bool   $navigation: Whether this is a navigational field or not. 
	  *                                     Navigational field values are not inserted in db. Defaults to false   
	  * @param bool     multiple:           Whether to return single object or multiple objects on fetch 
	  */
	 public function __construct(
	 	 string  $pmodel, 
	 	 string  $fmodel, 
	 	 ?string $field      = null, 
	 	 ?string $pk         = null, 
	 	 ?string $fk         = null,
	 	 bool    $navigation = false,
	 	 bool    $multiple   = false,
	 	 bool    $eager      = false
	 ){
	 	 $this->pmodel     = $pmodel;
	 	 $this->fmodel     = $fmodel;
	 	 $this->field      = $field;
	 	 $this->pk         = $pk;
	 	 $this->fk         = $fk;
	 	 $this->navigation = $navigation;
	 	 $this->multiple   = $multiple;
	 	 $this->eager      = $eager;
	 }
}

