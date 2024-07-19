<?php
declare(strict_types = 1);

namespace SaQle\Communication\Notifications;

enum NotifierTypes : string {
    case SMS   = "sms";
    case EMAIL = "email";
	case PUSH  = "push";
}
?>