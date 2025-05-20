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
 * Initiate Mpesa stk push to a users device
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Apis\Mpesa;

class StkPush{
	 /**
	  * Default timezone - if not provided, defaults to DEFAULT_TIMEZONE setting.
	  * @var string
	  * */
	 private string $timezone = DEFAULT_TIMEZONE;

	 /**
	  * Application consumer key as provided by safaricom on daraja portal - compulsory.
	  * @var string
	  * */
	 private string $consumer_key;

	 /**
	  * Application consumer secret as provided by safaricom on daraja portal - compulsory
	  * */
	 private string $consumer_secret;

	 /**
	  * The paybill/till number that will be receiving payments.
	  * @var string
	  * */
	 private string $business_short_code;

	 /**
	  * App API passkey as provided by safaricom on daraja portal - compulsory
	  * @var string
	  * */
	 private string $pass_key;

	 /**
	  * The access token url
	  * @var string
	  * */
	 private $access_token_url;

	 /**
	  * Stk push request url
	  * @var string
	  * */
	 private string $initiate_url;

	 /**
	  * This is the app url the results of the transaction will be sent to for your processing - compulsory
	  * @var string
	  * */
	 private string $callback_url;

     /**
     * New StkPush instance
     * @param array $settings: key => value array of configuration parameters
     **/
	 public function __construct(...$settings) : void{
	 	$this->timezone            = $settings['timezone'];
	 	$this->consumer_key        = $settings['consumer_key'];
	 	$this->consumer_secret     = $settings['consumer_secret'];
	 	$this->business_short_code = $settings['business_short_code'];
	 	$this->pass_key            = $settings['pass_key'];

	 	$env = $settings['environment'] ?? 'sandbox';
	 	$this->access_token_url = $env === 'sandbox' ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : '';
	 	$this->initiate_url = $env === 'sandbox' ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : '';
	 	$this->callback_url = $settings['callback_url'];
	 }

     /**
      * Send the push notification request
      * @param string $phone - the phone number of the customer
      * @param float $amount - the amount the customer is required to pay
      * @param string $acc_ref - the account reference
      * @param string $trans_deec - the transaction description
      * */
	 public function push(string $phone, float $amount, string $acc_ref, string $trans_desc){
	 	 date_default_timezone_set($this->timezone);

	 	 //get the timestamp, format YYYYmmddhms -> 20181004151020
         $timestamp = date('YmdHis');    

         //get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
         $password = base64_encode($this->business_short_code.$this->pass_key.$timestamp);

	 	 //set headers for the access token
         $headers = ['Content-Type:application/json; charset=utf8'];

         //get access token
         $curl = curl_init($this->access_token_url);
         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
         curl_setopt($curl, CURLOPT_HEADER, FALSE);
         curl_setopt($curl, CURLOPT_USERPWD, $this->consumer_key.':'.$this->consumer_secret);
         $result = curl_exec($curl);
         $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
         if((int)$status !== 200){
         	throw new ExpoException('Access token request failed!');
         }

         $result = json_decode($result);
         $access_token = $result->access_token;  
         curl_close($curl);

         //header for stk push
         $stkheader = ['Content-Type:application/json','Authorization:Bearer '.$access_token];

         //initiating the transaction
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $this->initiate_url);
         curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header

		 $curl_post_data = array(
		     'BusinessShortCode' => $this->business_short_code,
		     'Password'          => $password,
		     'Timestamp'         => $timestamp,
		     'TransactionType'   => 'CustomerPayBillOnline',
		     'Amount'            => $amount,
		     'PartyA'            => $phone,
		     'PartyB'            => $this->business_short_code,
		     'PhoneNumber'       => $phone,
		     'CallBackURL'       => $this->callback_url,
		     'AccountReference'  => $acc_ref,
		     'TransactionDesc'   => $trans_desc
		 );
         $data_string = json_encode($curl_post_data);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($curl, CURLOPT_POST, true);
         curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
         $curl_response = curl_exec($curl);
         return json_decode($curl_response);
	 }
}
