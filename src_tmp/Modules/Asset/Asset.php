<?php

namespace SaQle\Modules\Asset;

use SaQle\Core\Modules\{
	 Module,
	 ModuleBuilder
};

class Asset extends Module {

	 public function contribute(ModuleBuilder $module): void {

         $module->routes()
         ->prefix("")
         ->name("");
     } 
}