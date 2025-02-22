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
 * Represents an email object
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com>
 * */

namespace SaQle\Communication\Notifications\Email;

use SaQle\Communication\Notifications\INotification;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email implements INotification{
	 /**
	 * Email host
	 * @var string
	 */
	 private string $host = EMAIL_HOST;
	 
	 /**
	 * Email acc owner username
	 * @var string
	 */
	 private string $username = EMAIL_USERNAME;
	 
	 /**
	 * Email acc owner password
	 * @var string
	 */
	 private string $password = EMAIL_PASSWORD;
	 
	 /**
	 * Email host port
	 * @var integer
	 */
	 private int $port = 0;
	 
	 /**
	 * Email sender name and address
	 * @var array
	 */
	 protected array $from_address = [EMAIL_SENDER_ADDRESS, EMAIL_SENDER_NAME];

	 /**
	 * Reply to name and address
	 * @var array
	 */
	 protected array $reply_to_address = [EMAIL_SENDER_ADDRESS, EMAIL_SENDER_NAME];

     /**
     * Email recipient name and address
     * @var array
     */
	 protected array $to_address = [];

	 /**
	 * Email subject
	 * @var string
	 */
	 protected string $subject = "";

     /**
     * Email body
     * @var array
     */
	 protected $body = [];

	 /**
	 * Email cc addresses
	 * @var array
	 */
	 protected $cc_address = [];

	 /**
	 * Email bcc addresses
	 * @var array
	 */
	 protected $bcc_address = [];

	 /**
	 * Email attachments
	 * @var array
	 */
	 protected $attachments = [];

     /**
     * Create a new email instance
     * @param array $configurations
     */
	 public function __construct(...$configurations){
	 	 $this->port = (int)EMAIL_PORT;
		 $this->to_address  = [$configurations['rec_email'], $configurations['rec_name']];
		 $this->subject     = $configurations['subject'];
		 $this->body        = [
		     'is_html'                 => true,
			 'email_alt_body'          => $configurations['email_text'],
			 'email_content_file_path' => [$configurations['template_path'], $configurations['placeholder_values']]
		 ];
		 $this->cc_address  = $configurations['cc_address'] ?? $this->cc_address;
		 $this->bcc_address = $configurations['bcc_address'] ?? $this->bcc_address;
		 $this->attachments = $configurations['attachments'] ?? $this->attachments;
	 }
	 public function notify(){
		 return $this->send(
		     from_address: $this->from_address, 
			 to_address: $this->to_address,
			 reply_to_address: $this->reply_to_address,
			 subject: $this->subject,
			 body: $this->body,
			 cc_address: $this->cc_address,
			 bcc_address: $this->bcc_address,
			 attachments: $this->attachments
		 );
	 }
	 public function send(...$configurations){
		 $mail = new PHPMailer();
         $mail->isSMTP();

         $mail->Mailer = 'smtp';
         //$mail->SMTPDebug  = 1;  
         $mail->SMTPSecure = 'ssl';

         $mail->Host = $this->host;
         $mail->SMTPAuth = TRUE;
         
         $mail->Username = $this->username;
         $mail->Password = $this->password;
         $mail->Port = $this->port;
		 
		 if(array_key_exists("from_address", $configurations)){
			 $sender_name = isset($configurations['from_address'][1]) ? $configurations['from_address'][1] : "";
			 $mail->setFrom($configurations['from_address'][0], $sender_name);
		 }
		 if(array_key_exists("to_address", $configurations)){
			 $recipient_name = isset($configurations['to_address'][1]) ? $configurations['to_address'][1] : "";
			 $mail->addAddress($configurations['to_address'][0], $recipient_name);
		 }
		 if(array_key_exists("reply_to_address", $configurations)){
			 $reply_to_name = isset($configurations['reply_to_address'][1]) ? $configurations['reply_to_address'][1] : "";
			 $mail->addReplyTo($configurations['reply_to_address'][0], $reply_to_name);
		 }
		 if(array_key_exists("cc_address", $configurations)){
			 foreach($configurations['cc_address'] as $ccaddress){
				 $ccaddress_name = isset($ccaddress[1]) ? $ccaddress[1] : "";
				 $mail->addCC($ccaddress[0], $ccaddress_name);
			 }
		 }
		 if(array_key_exists("bcc_address", $configurations)){
			 foreach($configurations['bcc_address'] as $bccaddress){
				 $bccaddress_name = isset($bccaddress[1]) ? $bccaddress[1] : "";
				 $mail->addBCC($bccaddress[0], $bccaddress_name);
			 }
		 }
         $mail->Subject = $configurations['subject'] ?? "";
		 if(array_key_exists("body", $configurations)){
			 /*
			     an email body is an associative array that may have the following properties
				 is_html: true/false,
				 email_body: the body of email, email or plain text.
				 email_content_file_path: the path of the file to load as email content.
				 email_alt_body: plain text body for those email clients that will not be able to display html messages.
			 */
			 if(array_key_exists("is_html", $configurations['body']) && $configurations['body']['is_html']){
				 $mail->isHTML(TRUE);
			 }
			 if(array_key_exists("email_body", $configurations['body'])){
				 $mail->Body = $configurations['body']['email_body'];
			 }
			 if(array_key_exists("email_alt_body", $configurations['body'])){
				 $mail->AltBody = $configurations['body']['email_alt_body'];
			 }
			 if(array_key_exists("email_content_file_path", $configurations['body'])){
				 /*email_content_file_path is an array with actual file path at index 0, 
				 and an optional array o variables to be inserted into the fileat index 1*/
				 $email_content_file_path = $configurations['body']['email_content_file_path'][0];
				 $email_content_variables = isset($configurations['body']['email_content_file_path'][1]) && 
				 is_array($configurations['body']['email_content_file_path'][1]) ? $configurations['body']['email_content_file_path'][1] : array();
				 $file_contents = file_get_contents($email_content_file_path);
				 foreach($email_content_variables as $key => $value){
                     $file_contents = str_replace('{{ '.$key.' }}', $value, $file_contents);
                 }
				 $mail->Body = $file_contents;
			 }
		 }
		 if(array_key_exists("attachments", $configurations)){
			 //attachments is an array of arrays, where each array element in attachments reperesnt a single attachment
			 //each single attachment is an array of two elements, at index zero is file path to attach, at index one is
			 //the file name that recipeint will see.
			 foreach($configurations['attachments'] as $attach){
				 $file_display_name = isset($attach[1]) ? $attach[1] : "";
				 $mail->addAttachment($attach[0], $file_display_name);
			 }
		 }
		 return !$mail->send() ? $mail->ErrorInfo : true;
	 }
}
?>