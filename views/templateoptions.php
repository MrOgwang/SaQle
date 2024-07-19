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
 * Represents a template options object
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffe.omondi@saqle.com>
 * */

namespace SaQle\Views;

class TemplateOptions{
	/**
	 * The name of the template
	 * 
	 * @var string
	 */
	 private string $template;

	 /**
	 * The path to the template file
	 * 
	 * @var string
	 */
	 private string $path;

	 /**
	 * The context data for the template file
	 * 
	 * @var array
	 */
	 private array $context;

	 /**
	 * Whether this view instance was called from a controller or not. 
	 * This is to determine whether the controller needs to be run to provide context data.
	 * 
	 * @var bool
	 */
	 private bool $from_controller;
	 
	 /**
	 * Create a new template options instance
	 * 
	 * @param string $template
	 * @param string $path
	 * @param array  $context
	 * @param bool   $from_controller
	 */
	 public function __construct(string $template, string $path, array $context = [], bool $from_controller = true){
		 $this->template        = $template;
		 $this->path            = $path;
		 $this->context         = $context;
		 $this->from_controller = $from_controller;
	 }
	 
	 /**
	  * Get the template name
	  * 
	  * @return string
	  * */
	 public function get_template() : string{
		 return $this->template;
	 }
	 

	 /**
	  * Get the template path
	  * 
	  * @return string
	  * */
	 public function get_path() : string{
		 return $this->path;
	 }
	 
	 /**
	  * Get the template context
	  * 
	  * @return array
	  * */
	 public function get_context() : array{
		 return $this->context;
	 }
	 
	 /**
	  * Get from caller
	  * 
	  * @return bool
	  * */
	 public function get_from_controller() : bool{
		 return $this->from_controller;
	 }
	 

	 /**
	  * Add a context item
	  * 
	  * @param string $key - the item name
	  * @param mixed  $val - the value of the item
	  * */
	 public function add_to_context(string $key, $val){
		 $this->context[$key] = $val;
	 }
	 
	 /**
	  * Set the context
	  * 
	  * @param array $context
	  * */
	 public function set_context(array $context){
		 $this->context = $context;
	 }
	
}
?>