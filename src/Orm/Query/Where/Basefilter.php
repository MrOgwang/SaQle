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
 * Represents a base filter object.
 * 
 * Raw filters passed from the client by calling where method on the model manager will be translated into
 * this before they can be used by the query builder.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
namespace SaQle\Orm\Query\Where;

abstract class BaseFilter{
	 /**
	  * This denotes whether a filter object is a grouped filter
	  * or a simple one
	  * */
	 public protected(set) bool $grouped = false {
	 	 set(bool $value){
	 	 	$this->grouped = $value;
	 	 }

	 	 get => $this->grouped;
	 }
}
