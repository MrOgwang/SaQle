<?php

namespace SaQle\Core\Notifications\Mail\Transport;

use SaQle\Core\Support\Mailable;

interface TransportInterface {
     public function send(Mailable $mailable) : bool;
}