<?php

declare(strict_types = 1);

namespace SaQle\Lib\Routes;

use SaQle\Routing\Router;

Router::get(config('error.route'), config('error.component'))->name('app.error');

Router::get(
	 '/forms/options', 
	 'saqle.lib.autoform@select_field_options'
)
->name('select.options');

Router::get(
	 '/forms/options/cascade', 
	 'saqle.lib.autoform@select_cascade_options'
)
->name('select.cascade.options');

Router::get(
	 '/forms/options/search', 
	 'saqle.lib.autoform@select_search_options'
)
->name('select.search.options');

?>