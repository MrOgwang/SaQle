<?php
namespace SaQle\Communication\Notifications\Email;

use SaQle\Communication\Notifications\INotificationSetup;
use SaQle\Commons\StringUtils;

abstract class EmailSetup extends INotificationSetup{
	 use StringUtils;
	 protected $email_message;
	 protected $email_subject;
	 protected $email_template_path;
	 public function __construct(...$configurations){
		 $this->configurations = $configurations;
	 }
	 public function get_setup_data() : array{
		 return [
		     'rec_name' => $this->configurations['rec_name'],
			 'rec_email' => $this->configurations['rec_email'],
			 'subject' => $this->email_subject,
			 'email_text' => $this->set_template_context($this->email_message,  $this->configurations['placeholder_values']),
			 'template_path' => $this->email_template_path,
			 'cc_address' => $this->configurations['cc_address'] ?? [],
			 'bcc_address' => $this->configurations['bcc_address'] ?? [],
			 'attachments' => $this->configurations['attachments'] ?? [],
			 'placeholder_values' => $this->configurations['placeholder_values']
		 ];
	 }
}
