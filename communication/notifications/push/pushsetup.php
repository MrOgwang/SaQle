<?php
namespace SaQle\Communication\Notifications\Push;

use SaQle\Communication\Notifications\INotificationSetup;

abstract class PushSetup extends INotificationSetup{
	 protected string $title = '';
	 protected string $body = '';
	 protected array $data = [];
	 protected string $channel = '';
	 protected int $badge = 0;
	 protected bool $play_sound = false;
	 protected array $recipients = [];
	 public function __construct(...$configurations){
		 $this->configurations = $configurations;
	 }
	 public function get_setup_data() : array{
		 return [
		     'title' => $this->title,
		     'body' => $this->body,
		     'data' => $this->data,
		     'channel' => $this->channel,
		     'badge' => $this->badge,
		     'play_sound' => $this->play_sound,
		     'recipients' => $this->recipients,
		 ];
	 }
	 public abstract function get_recipients() : array;
}

