<?php
namespace SaQle\Http\Response;

enum ResponseType : string {
     case JSON = "json";
     case HTML = "html";
     case XML  = "xml";
     case TEXT = "text";
     case SSE  = "sse";
     case FILE = "file";
     case REDIRECT = "redirect";
}
