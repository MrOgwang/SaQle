<?php
namespace SaQle\Http\Request;

enum RequestScope : string {
     case API  = 'api';
     case WEB  = 'web';
}
