<?php

namespace SaQle\Core\Support;

enum AppStage : string {
     case INITIALIZING       = 'initializing';
     case REQUEST_BOOTSTRAP  = 'request_bootstrap';
     case REQUEST_RESOLUTION = 'request_resolution';
     case RESPONSE_BOOTSTRAP = 'response_bootstrap';
     case RESPONSE_SEND      = 'response_send';
     case TERMINATED         = 'terminated';
}