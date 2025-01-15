<?php
namespace SaQle\Communication\Notifications\Push;

use SaQle\Communication\Notifications\INotification;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class Push implements INotification{
	 /**
	 * Push notification title
	 * @var string
	 */
	 private string $title = '';

	 /**
	 * Push notification body
	 * @var string
	 */
	 private string $body = '';

	 /**
	 * Push notification data
	 * @var array
	 */
	 private array $data = [];

	 /**
	 * Push notification channel
	 * @var string
	 */
	 private string $channel = '';

	 /**
	 * Push notification badge
	 * @var integer
	 */
	 private int $badge = 0;

	 /**
	 * Play sound on device
	 * @var bool
	 */
	 private bool $play_sound = false;

	 /**
	 * Push notification recipient expo tokens
	 * @var array
	 */
	 private array $recipients = [];

	 /**
	 * Create a new push notification instance
	 * @param array $configurations
	 */
	 public function __construct(...$configurations){
		 $this->title      = $configurations['title'];
		 $this->body       = $configurations['body'];
		 $this->data       = $configurations['data'];
		 $this->channel    = $configurations['channel'];
		 $this->badge      = $configurations['badge'];
		 $this->play_sound = $configurations['play_sound'];
		 $this->recipients = $configurations['recipients'];
	 }
	 public function notify(){
	     if(count($this->recipients) > 0){
	         $expo = Expo::driver('file');
    		 //compose message
    		 $message = (new ExpoMessage())->setTitle($this->title)->setBody($this->body)->setData($this->data)->setChannelId($this->channel)->setBadge($this->badge);
    		 if($this->play_sound){
    			 $message->playSound();
    		 }
    		 (new Expo)->send($message)->to($this->recipients)->push();
	     }
	 }
}
?>