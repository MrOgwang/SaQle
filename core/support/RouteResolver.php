<?php

namespace SaQle\Core\Support;

interface RouteResolver {
	 public function routes() : array;
	 public function resolve($request) : string|int;
}