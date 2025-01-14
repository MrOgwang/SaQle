<?php
namespace SaQle\Middleware\Factory;

use SaQle\Middleware\Interface\IMiddlewareGroup;
use SaQle\Middleware\Group\{WebMiddlewareGroup, ApiMiddlewareGroup};
use SaQle\Http\Request\Request;
use SaQle\Middleware\Base\BaseMiddlewareGroup;

class MiddlewareGroup extends BaseMiddlewareGroup implements IMiddlewareGroup{
	 private string $request_type;

	 public function __construct(){
	 	 $request = Request::init();
	 	 $this->request_type = $request->is_api_request() ? 'api' : 'web';
	 }

	 public function get_middlewares() : array{
	 	 return match($this->request_type){
	 	 	 'web' => (new WebMiddlewareGroup())->get_middlewares(),
	 	 	 'api' => (new ApiMiddlewareGroup())->get_middlewares(),
	 	 };
	 }

	 public function handle(Request $request) : void{
	 	 $request_middlewares = $this->get_middlewares();
	 	 $middleware          = $request_middlewares[0];
         $middleware_instance = new $middleware();
         $this->assign_middlewares($middleware_instance, $request_middlewares, 1);
         $middleware_instance->handle($request);
	 }
}
?>