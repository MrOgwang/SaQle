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
 * The OnErrorResponse attribute is to be used on controller method to define 
 * a common object that you want the method to respond with
 * 
 * Sometimes you have multiple controller methods responding with similar data context keys.
 * This keys can be grouped in a class that can be used with RespondWith attribute
 * 
 * Usage example:
 * 
 * //data class
 * class User{
 *     protected string $first_name = '';
 *     protected string $last_name  = '';
 *     protected int    $age        = 25;
 * }
 * 
 * #[RespondWith(User::class, true)]
 * public function get_user() : HttpMessage {
 *     //do whatever here
 *     return new HttpMessage(HttpMessage::OK);
 * }
 * 
 * Note: Here, the user object will be provided as the data for the HttpMessage response.
 * 
 * Also Note: I maybe over engineering here. Only time will tell.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+2547 411 420 38>
 * */
namespace SaQle\Controllers\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class OnErrorResponse {
     protected string $model;
     protected bool   $from_feeback;

     public function __construct(string $model, bool $from_feeback) {
         $this->model = $model;
         $this->from_feeback = $from_feeback;
     }

     public function get_context() : array {
         $model = $this->model;
         return get_object_vars(new $model());
     }
}

