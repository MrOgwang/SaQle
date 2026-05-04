<?php
namespace SaQle\Core\FeedBack;

class FeedBack {

	 const CONTINUE                        = 100;
	 const SWITCHING_PROTOCALS             = 101;
	 const PROCESSING                      = 102;
	 const EARLY_HINTS                     = 103;

     const OK                              = 200;
     const CREATED                         = 201;
     const ACCEPTED                        = 202;
     const NON_AUTHORITATIVE_INFO          = 203;
     const NO_CONTENT                      = 204;
     const RESET_CONTENT                   = 205;
     const PARTIAL_CONTENT                 = 206;
     const MULTI_STATUS                    = 207;
     const ALREADY_REPORTED                = 208;
     const IM_USED                         = 226;

     const MULTIPLE_CHOICES                = 300;
     const MOVED_PERMANENTLY               = 301;
     const FOUND                           = 302;
     const SEE_OTHER                       = 303;
     const NOT_MODIFIED                    = 304;
     const TEMPORARY_REDIRECT              = 307;
     const PERMANENT_REDIRECT              = 308;

     const BAD_REQUEST                     = 400;
	 const UNAUTHENTICATED                 = 401;
     const PAYMENT_REQUIRED                = 402;
     const UNAUTHORIZED                    = 403;
     const NOT_FOUND                       = 404;
     const METHOD_NOT_ALLOWED              = 405;
     const NOT_ACCEPTABLE                  = 406;
     const PROXY_AUTHENTICATION_REQUIRED   = 407;
     const REQUEST_TIMEOUT                 = 408;
     const CONFLICT                        = 409;
     const GONE                            = 410;
     const LENGTH_REQUIRED                 = 411;
     const PRECONDITION_FAILED             = 412;
     const CONTENT_TOO_LARGE               = 413;
     const URI_TOO_LONG                    = 414;
     const UNSUPPORTED_MEDIA_TYPE          = 415;
     const RANGE_NOT_SATISFIABLE           = 416;
     const EXPECTATION_FAILED              = 417;
     const IM_A_TEAPOT                     = 418;
     const MISDIRECTED_REQUEST             = 421;
     const UNPROCESSABLE_ENTITY            = 422;
     const LOCKED                          = 423;
     const FAILED_DEPENDENCY               = 424;
     const TOO_EARLY                       = 425;
     const UPGRADE_REQUIRED                = 426;
     const PRECONDITION_REQUIRED           = 428;
     const TOO_MANY_REQUESTS               = 429;
     const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
     const UNAVAILABLE_FOR_LEGAL_REASONS   = 451;

     const INTERNAL_SERVER_ERROR           = 500;
     const METHOD_NOT_IMPLEMENTED          = 501;
     const BAD_GATEWAY                     = 502;
     const SERVICE_UNAVAILABLE             = 503;
     const GATEWAY_TIMEOUT                 = 504;
     const UNSUPPORTED_HTTP_VERSION        = 505;
     const VARIANT_ALSO_NEGOTIATES         = 506;
     const INSUFFICIENT_STORAGE            = 507;
     const LOOP_DETECTED                   = 508;
     const NOT_EXTENDED                    = 510;
     const NETWORK_AUTHENTICATION_REQUIRED = 511;

     public protected(set) int $code {
     	 set(int $value){
     	 	 $this->code = $value;
     	 }

     	 get => $this->code;
     }

     public protected(set) mixed $data {
     	 set(mixed $value){
     	 	 $this->data = $value;
     	 }

     	 get => $this->data;
     }

     public protected(set) string $message {
     	 set(string $value){
     	 	 $this->message = $value;
     	 }

     	 get => $this->message;
     }

     public protected(set) string $action {
     	 set(string $value){
     	 	 $this->action = $value;
     	 }

     	 get => $this->action;
     }

	 public function __construct(){
	 	 $this->code    = FeedBack::OK;
	 	 $this->message = $this->get_message($this->code);
	 	 $this->data    = null;
	 	 $this->action  = '';
	 }

	 public function set(int $code, mixed $data = null, ?string $message = null, string $action = ''){
	 	 $this->code    = $code;
	 	 $this->message = $message ? $message : $this->get_message($this->code);
	 	 $this->data    = $data;
	 	 $this->action  = $action;
	 }

	 protected function get_message(int $code){
		 $status_code = [
		 	 100 => 'Continue',
		 	 101 => 'Switching protocals',
		 	 102 => 'Processing',
		 	 103 => 'Early hints',

	         200 => 'Success',
	         201 => 'Created successfully',
	         202 => 'Request accepted',
	         203 => 'Non authoritative information',
	         204 => 'No content',
	         205 => 'Reset content',
	         206 => 'Partial content',
	         207 => 'Multi status',
	         208 => 'Already reported',
	         226 => 'IM used',

             300 => 'Multiple choices',
	         301 => 'Moved permanently',
	         302 => 'Found',
	         303 => 'See other',
	         304 => 'Not modified',
	         307 => 'Temporary redirect',
	         308 => 'Permanent redirect',

	         400 => 'Bad Request',
	         401 => 'Unauthenticated',
	         402 => 'Payment required',
	         403 => 'Unauthorized',
	         404 => 'Not Found',
	         405 => 'Method Not Allowed',
	         406 => 'Not Acceptable',
	         407 => 'Proxy authentication required',
	         408 => 'Request timeout',
	         409 => 'Conflict',
	         410 => 'Gone',
	         411 => 'Length required',
	         412 => 'Precondition failed',
	         413 => 'Content too large',
	         414 => 'Uri too long',
	         415 => 'Unsupported media type',
	         416 => 'Range not satisfiable',
	         417 => 'Expectation failed',
	         418 => 'Im a teapot',
	         421 => 'Misdirected request',
	         422 => 'Unprocessable entity',
	         423 => 'Locked',
	         424 => 'Failed dependency',
	         425 => 'Too early',
	         426 => 'Upgrade required',
	         428 => 'Precondition required',
	         429 => 'Too Many Requests',
	         431 => 'Request header fields too large',
	         451 => 'Unavailable for leagal reasons',
	        
	         500 => 'Internal Server Error',
	         501 => 'Method not implemented',
	         502 => 'Bad gateway'
	         503 => 'Service Unavailable',
	         504 => 'Gateway timeout',
	         505 => 'Unsupported http version',
	         506 => 'Variant also negotiates',
	         507 => 'Unsufficient storage',
	         508 => 'Loop detected',
	         510 => 'Not extended',
	         511 => 'Network authentication requred'
	     ];
	     
	     return $status_code[$code];
	 }
}
