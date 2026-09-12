<?php

 declare(strict_types = 1);

 namespace SaQle\Modules\Admin\Routes;

 use SaQle\Core\Support\Db;
 use SaQle\Routing\Router;

 require_once dirname(__DIR__)."/../Support.php";

 $db_schemas = Db::get_developer_schemas();

 foreach($db_schemas as $schema_name => $schema_config){

     $schema_class = $schema_config['schema'];

     $models = new $schema_class()->get_defined_models();

     foreach($models as $model_label => $model_class){
         register_resource_routes(false, $model_label, $model_class);
     }
 }

 $authorize  = trim(config('admin.authorization.global', ""));
 $middleware = config('admin.middleware.global', []);
 
 $overview = Router::get("/resources/overview/", 'saqle.admin.dashboard')
 ->layout(['saqle.admin.admin'])
 ->name('overview');

 if($authorize){
     $overview->authorize($authorize);
 }

 if($middleware){
     $overview->middleware($middleware);
 }
