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
 * Send an sms using GerySilicons apis
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Apis\Sms\GreySilicon;

use GuzzleHttp\Client;

class GreySiliconSms {
	 /**
	  * The api token as provided by grey silicon
	  * @var string
	  * */
	 private string $api_token;

	 /**
	  * The sms sender ID
	  * @var string
	  * */
	 private string $sender_id;

	 /**
	  * The sms endpoint url
	  * */
	 private string $base_url = "https://backend.greysilicon.com/";

	 /**
	  * The sms endpoint
	  * */
	 private string $endpoint = "api/v1/sms/send";

     /**
     * New sms instance
     * @param array $settings: key => value array of configuration parameters
     **/
	 public function __construct(...$settings){
	 	$this->api_token = $settings['api_token'];
	 	$this->sender_id = $settings['sender_id'];
	 }

     /**
      * Send the sms
      * @param string $phone - the phone number of the recipient
      * @param string $message - the message to send
      * */
	 public function send(string $phone, string $message){

	 	 //set headers
         $headers = [
         	 "Authorization" => "Bearer ".$this->api_token,
         	 "Content-Type" => "application/json"
         ];

         //set data
         $data = [
         	 "recipient" => $phone,
             "message" => $message,
             "sender_id" => $this->sender_id
         ];

         $client = new Client([
         	 'base_uri' => $this->base_url,
         ]);

         $response = $client->post($this->endpoint, [
             'json' => $data, // automatically sets Content-Type: application/json
		     'headers' => $headers
		 ]);

         return json_decode($response->getBody());
	 }

	 /**
      * Send the sms
      * @param string $phone - the phone number of the recipient
      * @param string $message - the message to send
      * */
	 public function curl_send(string $phone, string $message){

	 	 //set headers
         $headers = [
         	 "Authorization: Bearer ".$this->api_token,
         	 "Content-Type: application/json"
         ];

         //set data
         $data = json_encode([
         	 "recipient" => $phone,
             "message" => $message,
             "sender_id" => $this->sender_id
         ]);

         //send sms request
	 	 $curl = curl_init();
	 	 curl_setopt($curl, CURLOPT_URL, $this->url);
         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

         $response = curl_exec($curl);
         return json_decode($response);

	 }
}
