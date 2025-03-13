<?php
namespace SaQle\Http\Request\Data\Sources\Managers;

use SaQle\Http\Request\Data\Sources\Managers\Interfaces\IHttpDataSourceManager;
use SaQle\Http\Request\Data\Sources\{From, FromDb, FromCookie, FromBody, FromForm, FromHeader, FromPath, FromQuery};
use SaQle\Http\Request\Data\Sources\Managers\Types\{BodyDataSourceManager, CookieDataSourceManager, DbDataSourceManager, FormDataSourceManager, HeaderDataSourceManager, PathDataSourceManager, QueryDataSourceManager};

class HttpDataSourceManager implements IHttpDataSourceManager{

	 private IHttpDataSourceManager $manager;

	 public function __construct(From $from, ...$kwargs){
	 	 $this->manager = match($from::class){
	 	 	 FromDb::class     => new DbDataSourceManager($from, ...$kwargs),
	 	 	 FromCookie::class => new CookieDataSourceManager($from, ...$kwargs),
	 	 	 FromBody::class   => new BodyDataSourceManager($from, ...$kwargs),
	 	 	 FromForm::class   => new FormDataSourceManager($from, ...$kwargs),
	 	 	 FromHeader::class => new HeaderDataSourceManager($from, ...$kwargs),
	 	 	 FromPath::class   => new PathDataSourceManager($from, ...$kwargs),
	 	 	 FromQuery::class  => new QueryDataSourceManager($from, ...$kwargs)
	 	 };
	 }

	 public function get_value() : mixed {
	 	 return $this->manager->get_value();
	 }

	 public function is_valid() : bool {
	 	 return $this->manager->is_valid();
	 }
}
?>