<?php
namespace SaQle\Http\Request\Data\Sources\Managers;

use SaQle\Http\Request\Data\Sources\Managers\Interfaces\IHttpDataSourceManager;
use SaQle\Core\Support\BindFrom;
use SaQle\Http\Request\Data\Sources\Managers\Types\{CookieDataSourceManager, DbDataSourceManager, InputDataSourceManager, HeaderDataSourceManager, PathDataSourceManager, QueryDataSourceManager, SessionDataSourceManager, DiDataSourceManager};

class HttpDataSourceManager implements IHttpDataSourceManager {

	 private IHttpDataSourceManager $manager;

	 public function __construct(BindFrom $from, ...$kwargs){
	 	 $this->manager = match($from->from){
	 	 	 'db', 'database'  => new DbDataSourceManager($from, ...$kwargs),
	 	 	 'cookie'          => new CookieDataSourceManager($from, ...$kwargs),
	 	 	 'input'           => new InputDataSourceManager($from, ...$kwargs),
	 	 	 'header'          => new HeaderDataSourceManager($from, ...$kwargs),
	 	 	 'path'            => new PathDataSourceManager($from, ...$kwargs),
	 	 	 'query'           => new QueryDataSourceManager($from, ...$kwargs),
	 	 	 'session'         => new SessionDataSourceManager($from, ...$kwargs),
	 	 	 'di', 'container' => new DiDataSourceManager($from, ...$kwargs)
	 	 };
	 }

	 public function get_value() : mixed {
	 	 return $this->manager->get_value();
	 }

	 public function is_valid() : bool {
	 	 return $this->manager->is_valid();
	 }
}
