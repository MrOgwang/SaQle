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
 * The Exceptions attribute is to be used on controller method to list
 * all the exceptions to automatically catch when a controller method is executed.
 * 
 * This is a convenient way to avoid recurring try catch blocks inside the controller methods,
 * allowing the code inside the controller method to be short, clean and solely focused on what its
 * supposed to do.
 * 
 * Usage example:
 * 
 * #[Exceptions([
 *     InvalidArgumentException::class => HttpMessage::BAD_REQUEST, 
 *     KeyNotFoundException::class => HttpMessage::BAD_REQUEST
 * ])]
 * public function get_post() : HttpMessage {
 *     //do whatever here
 *     return new HttpMessage(HttpMessage::OK);
 * }
 * 
 * Works but, really just use the exceptions attribute
 * 
 * public function get_post() : HttpMessage {
 *     try{
 *        //do whatever here
 *        return new HttpMessage(HttpMessage::OK);
 *     }catch(InvalidArgumentException $e){
 *        return new HttpMessage(HttpMessage::BAD_REQUEST, 'error message here!');
 *     }catch(InvalidArgumentException $e){
 *        return new HttpMessage(HttpMessage::BAD_REQUEST, 'another error message here!');
 *     }
 * }
 *
 * 
 * Note: I maybe over engineering here. Only time will tell.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+2547 411 420 38>
 * */
namespace SaQle\Controllers\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Exceptions {
     public array $exceptions;

     public function __construct(array $exceptions = []) {
         $this->exceptions = $exceptions;
     }
}
?>
