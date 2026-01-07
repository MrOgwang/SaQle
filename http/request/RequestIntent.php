<?php
namespace SaQle\Http\Request;

enum RequestIntent: string {
     case SSE  = 'sse';
     case AJAX = 'ajax';
     case API  = 'api';
     case WEB  = 'web';
}
