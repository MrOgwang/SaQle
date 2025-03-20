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
 * The AuthUser attribute is to be used on controller method parameters to automatically inject the
 * currently logged in user into the method.
 * 
 * While the currently logged in user can be accessed directly from the controller's request property i.e $this->request->user,
 * while inside a controller method, I am providing this alternative 
 * 
 * 1. For convenience, I find calling $this->request->user all the time exhausting. Or am I too lazy? Who knows.
 * 
 * 2. Calling $this->request->user inside a controller method results into tight coupling between my controller methods and the request object.
 *    This is likely to be a problem should I want to use the controller class like a regular class.
 * 
 * 3. There maybe a future where I don't want the controller to have visibility of the request. How that may come about I don't know.
 * 
 * 4. If I use $this->request->user inside a controller method, I am constrained to the currently logged in user. With this attribute,
 *    I can easily have other user instances work with a method by simply removing the attribute. I do not have to change the method
 *    code.
 * 
 * Note: I maybe over engineering here. Only time will tell.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+2547 411 420 38>
 * */
declare(strict_types = 1);

namespace SaQle\Auth\Models\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class AuthUser{
	
}
?>