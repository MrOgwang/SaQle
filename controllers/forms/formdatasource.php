<?php
namespace SaQle\Controllers\Forms;
use SaQle\Dao\Field\Attributes\{PrimaryKey, ForeignKey, NavigationKey};
use SaQle\Dao\DbContext\DbContext;
use SaQle\Dao\Model\Model;
use SaQle\Controllers\Forms\{FormDataSourceSettings};
use SaQle\Dao\Field\Controls\FormControl;
use SaQle\Commons\StringUtils;
use stdClass;
class FormDataSource{
	use StringUtils;
	private string  $table;
	private string  $dao;
	private ?array  $fields   = null;
	private ?string $aliase   = null;
	private bool    $multiple = false;
	private string  $primary  = "";
	private array   $children = [];
	private ?stdClass $sdata   = null;
	public function __construct(
		 string    $dao, 
		 DbContext $context, 
		 ?array    $fields = null, 
		 ?string   $aliase = null, 
		 ?stdClass $sdata  = null,
	)
	{                
		$this->table   = $context->get_dao_table_name($dao);
		$this->dao     = $dao;
		$this->fields  = [];
		$this->aliase  = $aliase;
		$this->sdata   = $sdata;

		$dmodel        = new Model(new $dao());
		$this->primary = $dmodel->get_primary_key_name();
		$dmodel        = null;
		if(!$fields){
			 $reflector     = new \ReflectionClass($dao);
	         $properties    = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);
			 foreach($properties as $p){
			 	 $property_type   = str_replace("?", "", $p->getType()); 
				 $property_name   = $p->getName();
				 if($property_type === "SaQle\Dao\Field\Interfaces\IField"){
				 	 $pinstance = $p->getValue($dao);
				 	 $this->fields[$property_name] = $pinstance->get_field_data_source();
				 }else{
				 	 $property_value  = $p->getDefaultValue();
				     $attributes      = $p->getAttributes(FieldDataSource::class);
					 if($attributes){
					 	$ctrl_attributes = $p->getAttributes(FormControl::class);
					 	if($ctrl_attributes){
					 		$control_instance = $ctrl_attributes[0]->newInstance();
					 		$this->fields[$property_name] = (new FieldDataSource(name: $property_name, source: 'form', control: $control_instance->get_name(), value: $property_value, is_file: $control_instance->get_type() == 'file' ? true : false));
					 		$control_instance = null;
					 	}else{
					 		 $this->fields[$property_name] = (new FieldDataSource(name: $property_name, source: 'defined', control: $property_name, value: $property_value));
					 	}
					 }
				 }

				 $nav_attributes  = array_merge($p->getAttributes(NavigationKey::class), $p->getAttributes(ForeignKey::class));
				 $fds_attributes  = $p->getAttributes(FormDataSourceSettings::class);

				 if(!$nav_attributes || !$fds_attributes)
				 	continue;

				 $fn_key_instance = $nav_attributes[0]->newInstance();
				 if(!$fn_key_instance->get_include())
				 	continue;

				 $fdao         = $fn_key_instance->get_fdao();
			 	 $fn_sproperty = $fn_key_instance->get_field();

			 	 $model        = new Model(new $fdao());
			 	 $primary_key  = $model->get_primary_key_name();
			 	 $model        = null;
			 	 
			 	 $fds_settings = $fds_attributes[0]->newInstance();
		 	 	 $from_form    = $fds_settings->get_from_form();
		 	 	 $from_sdata   = $fds_settings->get_from_sdata();
		 	 	 $pre_defined  = $fds_settings->get_pre_defined();
		 	 	 $child_count  = 0;

		 	 	 if(!$fn_key_instance->get_multiple()){
		 	 	 	 $child        = $sdata ? $sdata->$fn_sproperty : null;
		 	 	 	 $child_source = new FormDataSource(
			 	 	 	 dao:       $fdao,
			 	 	 	 context:   $context,
			 	 	 	 sdata:     $child
			 	 	 );
			 	 	 $child_source->from_form(fields: $from_form);
			 	 	 $child_source->set_primary($primary_key);
				 	 $child_source->set_multiple(false);
				 	 $this->children[] = $child_source;
				 	 $this->children   = array_merge($this->children, $child_source->get_children());
				 	 continue;
		 	 	 }

		 	 	 $children         = $sdata ? $sdata->$fn_sproperty : [];
			 	 foreach($children as $cindex => $child){
			 	 	 $child_source = new FormDataSource(
			 	 	 	 dao:       $fdao,
			 	 	 	 context:   $context,
			 	 	 	 sdata:     $child
			 	 	 );
			 	 	 $child_source->from_form(fields: $from_form, findex: $cindex);
			 	 	 $child_source->set_primary($primary_key);
				 	 $child_source->set_multiple(false);
				 	 $this->children[] = $child_source;
				 	 $this->children   = array_merge($this->children, $child_source->get_children());
			 	 }
			 	 $child_count = count($children);

			 	 $new_child_source = new FormDataSource(
		 	 	 	 dao:       $fdao,
		 	 	 	 context:   $context,
		 	 	 	 sdata:     null
		 	 	 );
		 	 	 $new_child_index = $child_count;
		 	 	 $new_child_source->from_form(fields: $from_form, findex: $new_child_index);
		 	 	 $new_child_source->from_sdata(array_keys($from_sdata), array_values($from_sdata), $sdata);
		 	 	 $new_child_source->pre_defined(array_keys($pre_defined), array_values($pre_defined));
			 	 $new_child_source->set_multiple(false);
			 	 $this->children[] = $new_child_source;
			 }
		}else{
			 foreach($fields as $i => $f){
			 	$this->fields[$f->get_name()] = $f;
			 }
		}
	}

    public function get_primary(){
    	return $this->primary;
    }

	public function get_table(){
		return $this->table;
	}

	public function get_dao(){
		return $this->dao;
	}

	public function get_fields(){
		return $this->fields;
	}

	public function get_aliase(){
		return $this->aliase;
	}

	public function get_sdata(){
		return $this->sdata;
	}

	public function __get($name){
		 return $this->get($name);
    }
    public function get($name){
    	 $field_data_source = $this->fields[$name] ?? null;
    	 if(!$field_data_source){
    	 	throw new \Exception("The field {$name} does not exist in the form data source field list!");
    	 }

		 return $field_data_source;
    }

    /**
     * Remove listed fields from the data source
     * @param array
     * */
    public function except(array $fields){
    	foreach($fields as $f){
    		if(array_key_exists($f, $this->fields)){
    			unset($this->fields[$f]);
    		}
    	}
    }

    public function from_form(array $fields, ?array $controls = null, int $findex = -1){
    	foreach($fields as $fc => $f){
    		if(array_key_exists($f, $this->fields)){
    			$this->fields[$f]->set_source("form");
    			$control = $controls[$fc] ?? $f;
    			if($findex !== -1){
    				$control = $control."_".$findex;
    			}
    			$this->fields[$f]->set_control($control);
    		}
    	}
    }

    public function from_sdata(array $fields, array $sproperties, $sdata){
    	foreach($fields as $fc => $f){
    		if(array_key_exists($f, $this->fields)){
    			$this->fields[$f]->set_source("defined");
    			$value = self::get_property_value($sproperties[$fc], $sdata);
    			$this->fields[$f]->set_value($value);
    		}
    	}
    }

    public function is_file(array $fields){
    	foreach($fields as $fc => $f){
    		if(array_key_exists($f, $this->fields)){
    			$this->fields[$f]->set_is_file(true);
    		}
    	}
    }

    public function pre_defined(array $fields, array $values){
    	foreach($fields as $fc => $f){
    		if(array_key_exists($f, $this->fields)){
    			$this->fields[$f]->set_source("defined");
    			$value = $values[$fc] ?? null;
    			$this->fields[$f]->set_value($value);
    		}
    	}
    }

    public function from_result(array $fields, array $values){
    	foreach($fields as $fc => $f){
    		if(array_key_exists($f, $this->fields)){
    			$this->fields[$f]->set_source("result");
    			$value = $values[$fc] ?? null;
    			$this->fields[$f]->set_value($value);
    		}
    	}
    }

    public function add(FieldDataSource $source){
    	$this->fields[$source->get_name()] = $source;
    }

    public function set_multiple(bool $multiple){
    	$this->multiple = $multiple;
    }

    public function get_multiple(){
    	return $this->multiple;
    }

    public function set_primary(string $primary){
    	$this->primary = $primary;
    }

    public function get_datasource_name(){
    	return $this->aliase ? $this->table.":".$this->aliase : $this->table.":".$this->table;
    }

    public function get_children(){
    	return $this->children;
    }
}
?>