<?php
namespace SaQle\Auth\Models;

use SaQle\Orm\Entities\Model\Schema\{
	 Model, 
	 Table,
	 Presenter
};
use SaQle\Auth\Identity\Tenant\Interfaces\TenantInterface;

class BaseTenant extends Model implements TenantInterface {

	 protected function table_schema(Table $table) : void {

	 	 $table->name('tenants');

	 	 $table->primary_key('tenant_id');

		 $table->fields([ 
		     'tenant_name' => Table::char_field()->required()->unique(),
		     'slug' => Table::slug_field()->compute(function($model){
		     	  return slugify($model->tenant_name);
		     })->required(),
		     'url' => Table::char_field()->compute(function($model){
		     	 return '/saqle/tenants/'.slugify($model->tenant_name).'/manage';
		     })->required(),
		 ]);

		 $table->with_user_audit(false); 
		 $table->with_timestamps(true);
		 $table->with_soft_delete(false);
	 }

     public function get_id() : mixed {
     	 return $this->tenant_id;
     }

     public function get_name() : string {
     	 return $this->tenant_name;
     }

     #[Presenter(name: 'admin')] 
     public function admin_presenter(){
     	 return [
     	 	 'tenant_id'   => null,
     	 	 'tenant_name' => null,
     	 	 'slug'        => null,
     	 	 'url'         => function($model){
     	 	 	 return "<a target='_blank' href='{$model->url}'>Manage</a>";
     	 	 }
     	 ];
     }
}
