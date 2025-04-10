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
 * The service attribute is used to type hint services in controller methods.
 * 
 * This attribute is used especially where a service and its methods haave 
 * been listed to be automatically observed. 
 * 
 * Where automatic observation of a service and its methods is not required,
 * the service can be typed hinted directly or even instantiated from its own class
 * inside the controller method.
 * 
 * Usage example:
 * 
 * public function post(#[ObservedService(ActualService::class)] $s, string $param1, int $param2){
 *      $result = $s->calling_service_method($param1, $param2);
 * }
 * 
 * Where a service is not auto observed, the service hinting will be done as usual
 * 
 * public function post(ActualService $s, string $param1, int $param2){
 *      $result = $s->calling_service_method($param1, $param2);
 * }
 * 
 * The service can even be called instatiated directly inside the controller method
 * 
 * * public function post(string $param1, int $param2){
 *      $s = new ActualService();
 *      $result = $s->calling_service_method($param1, $param2);
 * }
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+254741142038>
 * */
namespace SaQle\Core\Services\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ObservedService {
	 public protected(set) string $service {
	 	 set(string $value){
	 	 	 $this->service = $value;
	 	 }

	 	 get => $this->service;
	 }

	 public function __construct(string $service){
	 	 $this->service = $service;
	 }
}
?>