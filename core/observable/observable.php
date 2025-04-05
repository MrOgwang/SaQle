<?php
namespace SaQle\Core\Observable;

interface Observable{
     public function attach(Observer $observer);
     public function detach(Observer $observer);
     public function notify();
	 public function status();
}
?>