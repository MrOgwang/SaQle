<?php

namespace SaQle\Core\Notifications\Mail\Transport;

use SaQle\Core\Notifications\Mail\Mailable;

interface TransportInterface {
     public function send(Mailable $mailable) : bool;
}