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
 * Define a one to one relationship between two models. To create a complicate relationship
 * between model A and B:
 * In A, have a one to one field pointing to B,
 * In B, have a one to one field pointing to A
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

declare(strict_types = 1);
namespace SaQle\Orm\Entities\Field\Relations;

use SaQle\Orm\Entities\Field\Relations\Base\BaseRelation;

class One2One extends BaseRelation{
	 public function __construct(
	 	 string   $pmodel,
	 	 string   $fmodel,
	 	 ?string  $field      = null, 
	 	 ?string  $pk         = null,
	 	 ?string  $fk         = null,
	 	 bool     $navigation = false,
	 	 bool     $multiple   = false,
	 	 bool     $eager      = false
	 ){
	 	parent::__construct($pmodel, $fmodel, $field, $pk, $fk, $navigation, false, $eager);
	 } 
}

