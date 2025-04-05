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
 * The layout route class:
 * 
 * This framework has no inherits mechanism for its templates. Each route is
 * pointed at a spcific controller where it is assumed that an associated template exists.
 * A route may also be pointed at a specific template, where no controller exists for such
 * a template.
 * 
 * Templates(html) are not fully formed, therefore a complete page is formed
 * by defining a layout route. When a request hits a given url,
 * the parent url's controller or template will be called to complete the page.
 * 
 * This structure is defined in the routes using the LayoutRoute object
 * 
 * Note: If I don't make sense to you, that's because am also struggling to 
 * understand what I have written up there. Just know that you can define your 
 * page structure from the routes file. This will be clear from the examples
 * and the documentation.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */
declare(strict_types = 1);

namespace SaQle\Routes;

use SaQle\Routes\Interfaces\IRoute;
use SaQle\Core\Assert\Assert;
use Closure;

class LayoutRoute implements IRoute{
     //the parent route
     public protected(set) ?Route $parent = null {
         set(?Route $value){
             $this->parent = $value;
         }

         get => $this->parent;
     }

     //an array of the children routes
     public protected(set) array $children = [] {
         set(array $value){
             //asset array of route objects
             Assert::allIsInstanceOf($value, IRoute::class, 'One or more items in children is not a route object!');
             $this->children = $value;
         }

         get => $this->children;
     }

     //create a new layout route object
	 public function __construct(Route $parent, array $children){
         $this->parent = $parent;
         $this->children = $children;
	 }

     public function matches() : array {
         $parent_matches = $this->parent->matches();
         if($parent_matches[0] === true)
             return [true, [$parent_matches[0], $parent_matches[1], $this->children[0]], $this->parent];

         foreach($this->children as $child){
             $child_matches = $child->matches();
             if($child_matches[0] === true){
                 return [true, $child_matches, $this->parent];
             }
         }

         return [false, false, null];
     }
}
?>