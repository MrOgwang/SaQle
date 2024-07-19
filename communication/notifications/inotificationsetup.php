<?php
namespace SaQle\Communication\Notifications;

/**
* Notification setup interface
*/
abstract class INotificationSetup{
    protected $configurations;
	public abstract function get_setup_data() : array;
}
?>