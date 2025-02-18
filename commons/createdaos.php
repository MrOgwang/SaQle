
<?php
require_once UTILITIES. "/utility_functions.php";
require_once UTILITIES. "/Dao/dao.php";
class VoFactory extends ModelObject
{
	 public function __construct($db_name, $db_user = "root", $db_password = "")
	 {
		 parent::__construct($db_name, $db_user, $db_password, "");
	 }
	 
	 public function createMimeTypes()
	 {
		 $types = '';
		 $h3_pattern = "[<h3>.+</h3>]i";
         $groups = preg_split($h3_pattern, $types);
		 preg_match_all($h3_pattern, $types, $data);
		 //clean the groups;
		 $clean_groups = array();
		 foreach($data[0] as $g){
			 $value = trim(str_replace("</h3>", "", str_replace("<h3>", "", $g)));
			 $span_pattern = "/<span\s+class=\"[^\"]*\"\s+id=\"[^\"]*\">(.*)<\/span>/iU";
			 preg_match_all($span_pattern, $value, $matches);
			 $value2 = $matches[1][0];
			 $clean_groups[] = $value2;
		 }
		 $cleaner_groups = array();
		 foreach($clean_groups as $g)
		 {
			 $url_pattern = "/<a\s+href=\"[^\"]*\">(.*)<\/a>/iU";
			 preg_match_all($url_pattern, $g, $matches);
			 $cleaner_groups[] = count($matches[1]) > 0 ? $matches[1][0] : $g;
		 }
		 unset($groups[0]);
		 $groups = array_values($groups);
		 $group_topics = array();
		 for($g = 0; $g < count($groups); $g++)
		 {
			 $topics_string = $groups[$g];
			 $li_pattern = "[<li>.+</li>]i";
			 preg_match_all($li_pattern, $topics_string, $data);
			 $topics = array();
			 foreach($data[0] as $li)
			 {
				 $value = str_replace("</li>", "", str_replace("<li>", "", $li));
				 $url_pattern = "/<a href=\"([^\"]*)\">(.*)<\/a>/iU";
				 preg_match_all($url_pattern, $value, $matches);
				 if(count($matches[2]) > 0)
				 {
					 foreach($matches[2] as $t)
					 {
						 $topics[] = ucwords($t);
					 }
				 }
				 else
				 {
					 $topics[] = ucwords($value);
				 }					
			 }
			 $title = ucwords($cleaner_groups[$g]);
			 $group_topics[$title] = $topics;
		 }
		 //print_r($group_topics);
		 
		 $academic_disciplines = "define('A_DISCIPLINES', array(\n";
		 $group_index = 0;
		 foreach($group_topics as $key=>$val)
		 {
			 $academic_disciplines .= "\t\t\t\t\t\t'".$key."'=>array(\n";
			 for($d = 0; $d < count($val); $d++)
			 {
				 $academic_disciplines .= "\t\t\t\t\t\t\t\t\t'".addslashes($val[$d])."'";
				 if($d < count($val) - 1)
				 {
					 $academic_disciplines .= ",";
				 }
				 $academic_disciplines .= "\n";
			 }
			 $academic_disciplines .= "\t\t\t\t\t\t)";
			 if($group_index < count($group_topics) - 1)
			 {
				 $academic_disciplines .= ",\n";
			 }
			 else
			 {
				 $academic_disciplines .= "\n";
			 }
			 $group_index += 1;
		 }
		 $academic_disciplines .= "));";
		 /*$td_pattern = "[<td>.+</td>]i";
		 $data = array();
		 preg_match_all($td_pattern, $types, $data);
		 $row_flag = 0;
		 $mime_types = array();
		 foreach($data[0] as $td)
		 {
			 $value = str_replace("</td>", "", str_replace("<td>", "", $td));
			 if($row_flag == 0){
				 $mime_types[] = array("type"=>"", "mime"=>"", "extension"=>"");
			 }
			 if($row_flag < 3){
				 if($row_flag == 0){
					  $mime_types[count($mime_types) - 1]['type'] = $value;
				 }elseif($row_flag == 1){
					  $mime_types[count($mime_types) - 1]['mime'] = $value;
				 }elseif($row_flag == 2){
					  $mime_types[count($mime_types) - 1]['extension'] = $value;
				 }
			 }
			 if($row_flag == 3){
				 $row_flag = 0;
			 }else{
				 $row_flag += 1;
			 }
		 }*/
		 /*$document_mime_types = "static $"."document_mime_types = array(\n";
		 foreach($mime_types as $mime)
		 {
			 if(strpos($mime['type'], "Microsoft") === 0){
				 $document_mime_types .= "'".$mime['mime']."'=>array('name'=>'".$mime['type']."', 'extension'=>'".$mime['extension']."', 'icon_url'=>"."RSC_BASE_URL.'"."public/SiteAssets/images/layout/icons/textfileicon.png'),\n";
			 }
		 }
		 $document_mime_types .= ")\n";
		 echo $document_mime_types;*/
		 /*foreach($mime_types as $mime)
		 {
			 if(!array_key_exists($mime['mime'], self::$image_mime_types)
				 && !array_key_exists($mime['mime'], self::$video_mime_types)
			     && !array_key_exists($mime['mime'], self::$text_mime_types)
				 && !array_key_exists($mime['mime'], self::$audio_mime_types)
				 && strpos($mime['type'], "Microsoft") === false
			 ){
				 print_r($mime);
			 }
		 }*/
	 }
     /*
	     - a value object is a class that reflects a database table. objects instances of this class
		  will be used to manipulate the database tables they represent.
		  @param string $table_definition: is an object array that represents the states of the tables in a db.
		  @param string $vo_name: the name of the class.optional. defaults to null. Will be the same as table name with all spaces and underscores removed and the name caplitalised.
		  @param string $vo_path: where to save the created class.
		  @param array $table_columns: an array of columns as they are required to appear in the database.
		 - the array of columns is as follows:
		 $columns = array(
		     array("column"=>"column_name", "type"=>"type", "length"=>"length", "dafault"=>"default_value", "collation"=>"collation", "attribute"=>"attribute", "null"=>true, "index"=>true, "auto"=>true),
		 );
	 */
     public function createValueObject($db_name, $table_name, $table_columns, $vo_name, $vo_path = NULL)
	 {
		 $db_variables = array(
		     "saqlecom_ad"=>"$"."GLOBALS['configurations']['ACTIVE_DIRECTORY_DB']",
			 "saqlecom_vitele"=>"$"."GLOBALS['configurations']['VITELE_DB']",
			 "saqlecom_remaso"=>"$"."GLOBALS['configurations']['REMASO_DB']",
			 "saqlecom_admin"=>"$"."GLOBALS['configurations']['ADMIN_DB']",
			 "saqlecom_newsfeed"=>"$"."GLOBALS['configurations']['NEWSFEED_DB']",
			 "saqlecom_financials"=>"$"."GLOBALS['configurations']['FIN_DB']"
		 );
		 $default_columns = array("tenant_token", "date_added", "added_by", "last_modified", "modified_by", "deleted");
		 $clean_columns = array();
		 //remove the default columns from the table definition.
		 foreach($table_columns as $col)
		 {
			 if(!in_array($col->name, $default_columns))
			 {
				 array_push($clean_columns, $col);
			 }
		 }
		 $vo_path = !is_null($vo_path) && $vo_path !== "" ? $vo_path : UTILITIES ."/Vo/".strtolower($vo_name).".php";
		 $properties = array();
		 $created_class = "<?php";
		 $created_class .= "\nrequire_once UTILITIES."."'/Vo/tblwrapper.php';";
		 $created_class .= "\nclass ".$vo_name." extends TblWrapper\n{";
		 $created_class .= "\n\tpublic function __construct()\n\t{";
		 $created_class .= "\n\t\tparent::__construct(".$db_variables[$db_name].", '".$table_name."');";
		 foreach($clean_columns as $col)
		 {
			 $created_class .= "\n\t\t$"."this->bind(array('name'=>'".$col->name."', 'type'=>'".$col->type."', 'primary'=>".$col->is_primary_key.", 'auto'=>".$col->auto_increment.", 'null'=>".$col->is_null.", 'default'=>'".$col->default."', 'length'=>".$col->length."));";
		 }
		 $created_class .= "\n\t}";
		 $created_class .= "\n}";
		 $created_class .= "\n?>";
		 $fh = fopen($vo_path,"w");
		 fwrite($fh, $created_class);
		 fclose($fh);
	 }

     private function getClassPropertyInitialValue($property)
	 {
		 $initial_value = "NULL";
		 if($property['null'] !== true)
		 {
			 if( array_key_exists("default", $property) )
			 {
				 $initial_value = ( $property['default'] != "" ) ? $property['default'] : '""';
			 }
			 else
			 {
				 switch($property['type'])
			     {
				     case "CHAR":
				     case "VARCHAR":
				     case "TINYTEXT":
				     case "TEXT":
				     case "MEDIUMTEXT":
				     case "LONGTEXT":
					     $initial_value = '""';
					 break;
					 case "TINYINT":
				     case "SMALLINT":
				     case "MEDIUMINT":
				     case "INT":
				     case "BIGINT":
				     case "DECIMAL":
					 case "FLOAT":
				     case "DOUBLE":
				     case "REAL":
					 case "BOOLEAN":
					     $initial_value = 0;
					 break;
			     }
			 }
		 }
		 return $initial_value;
	 }

	 public function createSystemValueObjects($db_name, array $value_objects = NULL)
	 {
		 $database_table_definitions = $this->getDatabaseTablesDefinition($db_name); //get the definition of tables from the database itself.
		 $tables_to_consider = array();
		 if(!is_null($value_objects) && !empty($value_objects) && is_array($value_objects))
		 {
			 foreach($value_objects as $vo)
			 {
				 if(isset($vo['table_name']) && $vo['table_name'] !== "")
				 {
					 $tables_to_consider[$vo['table_name']] = new stdClass();
				     $tables_to_consider[$vo['table_name']]->columns = $database_table_definitions[$vo['table_name']];
				     $tables_to_consider[$vo['table_name']]->vo_name = isset($vo['vo_name']) && $vo['vo_name'] !== "" ? $vo['vo_name'] : ucwords($vo['table_name']);
				     $tables_to_consider[$vo['table_name']]->vo_path = isset($vo['vo_path']) && $vo['vo_path'] !== "" ? $vo['vo_path'] : "";
				 }
			 }
		 }
		 else
		 {
			 foreach($database_table_definitions as $key=>$value)
			 {
				 $tables_to_consider[$key] = new stdClass();
				 $tables_to_consider[$key]->columns = $database_table_definitions[$key];
				 $tables_to_consider[$key]->vo_name = ucwords($key);
				 $tables_to_consider[$key]->vo_path = "";
			 }
		 }
		 foreach($tables_to_consider as $key=>$tbl_def) $this->createValueObject($db_name,  $key, $tbl_def->columns, $tbl_def->vo_name, $tbl_def->vo_path);
	 }

     public function getDatabaseTablesDefinition($db_name, array $tables = NULL)
	 {
		 $database_tables = $this->getAllDatabaseTables($db_name);
		 $selected_tables = array();
		 if(!is_null($tables) && !empty($tables))
		 {
			 foreach($database_tables as $table)
			 {
				 if(in_array($table->TABLE_NAME, $tables))
				 {
					 array_push($selected_tables, $table);
				 }
			 }
		 }
		 else
		 {
			 $selected_tables = $database_tables;
		 }
			 
		 $database_tables_definition = array();
		 foreach($selected_tables as $table)
		 {
			 $database_tables_definition[$table->TABLE_NAME] = array();
			 $table_description = $this->getTableDescription($table->TABLE_NAME);
			 foreach($table_description as $column)
			 {
				 $type_length = explode("(", $column->Type);
				 $type = strtoupper($type_length[0]);
				 $length = (count($type_length ) > 1) ? (int)explode(")", $type_length[1])[0] : 0;
				 $is_null = $column->Null === "NO" ? "false" : "true";
				 $index = $column->Key === "PRI" ? "true" : "false";
				 $auto = $column->Extra === "auto_increment" ? "true" : "false";
				 $column_definition = array("name"=>$column->Field,"type"=>$type,"length"=>$length, "default"=>$column->Default, "is_null"=>$is_null, "is_primary_key"=>$index, "auto_increment"=>$auto);
				 array_push($database_tables_definition[$table->TABLE_NAME], (Object)$column_definition);
			 }
		 }
		 return $database_tables_definition;
	 }
	 
	 private function constructDefaultValueObjectName($table_name)
	 {
		 //replace all the underscores and dashes with spaces.
		 $table_name = str_replace("_", " ", $table_name);
		 $table_name = str_replace("-", " ", $table_name);
		 //capitalise every word.
		 $table_name = ucwords($table_name);
		 //remove the spaces.
		 $table_name = str_replace(" ", "", $table_name);
		 return $table_name;
	 }
	 
	 
	 public function defineDatabaseTables()
	 {
		 $default_columns = array("date_added", "added_by", "last_modified", "modified_by", "deleted");
		 $database_tables = array();
		 if(SYSTEM_VALUE_OBJECTS)
		 {
			for($t = 0; $t < count(SYSTEM_VALUE_OBJECTS); $t++)
			{
				if(!is_null(SYSTEM_VALUE_OBJECTS[$t]['table_name']))
				{
					$this->table = SYSTEM_VALUE_OBJECTS[$t]['table_name'];
					$table_defined = $this->isTableDefined($this->table);
					if(!$table_defined->is_defined)
					{
						 $fields = $this->getTableDescription();
                         
						 $table_definition = "\n" .'//' .$this->table .' table definition.' ."\n";
						 $table_definition .= 'array' ."\n";
						 $table_definition .= '("table" => "'. $this->table. '",' ."\n";
						 $table_definition .= '"columns" => array' ."\n";
						 $table_definition .= '(' ."\n";
						 $column_definitions = array();
						 foreach($fields as $f)
						 {
							 if(!in_array($f->Field, $default_columns))
							 {
								 array_push($column_definitions, $this->constructColumnDefinition($f));
							 }
						 }
						 $table_definition .= $this->constructStringFromArray($column_definitions, ",\n", NULL, false);
						 $table_definition .= "\n" .')';
						 $table_definition .= "\n" .')';
						 array_push($database_tables, $table_definition);
					}
				}
			}
		 }
	 }

	 private function constructColumnDefinition($field)
	 {
		 $type_length = explode("(", $field->Type);
		 $type = strtoupper($type_length[0]);
		 $length = (count($type_length ) > 1) ? (int)explode(")", $type_length[1])[0] : 0;

		 $column_definition = "\t".'array("column"=>"';
		 $column_definition .= $field->Field;
		 $column_definition .= '", "type"=>"';
		 $column_definition .= $type;
		 $column_definition .= '", "length"=>';
		 $column_definition .= $length;
		 $column_definition .= ', "default"=>"';
		 $column_definition .= $field->Default;
		 $column_definition .= '", "collation"=>"", "attribute"=>"", "null"=>';
		 $column_definition .= ($field->Null === "NO") ? 'false' : 'true';
		 $column_definition .= ', "index"=>"';
		 $column_definition .= ($field->Key === "PRI") ? 'primary' : '';
		 $column_definition .= '", "auto"=>';
		 $column_definition .= ($field->Extra === "auto_increment") ? 'true' : 'false';
		 $column_definition .= ')';
		 return $column_definition;
	 }

     /*
	     - check whether a database tables definition exists in the configurations file.
		 - database tables definitions are contained in a constant array called DATABASE_TABLES
		 @param string $table_name: the name of the table whose definition is to be checked.
		 @return object $table_defined
	 */
	 private function isTableDefined($table_name)
	 {
		 $table_defined = new stdClass();
		 $table_defined->is_defined = false;
		 $table_defined->columns = array();
		 if(DATABASE_TABLES)
		 {
			 for($t = 0; $t < count(DATABASE_TABLES); $t++)
			{
				if(DATABASE_TABLES[$t]['table'] == $table_name)
				{
					$table_defined->is_defined = true;
					$table_defined->columns  = DATABASE_TABLES[$t]['columns'];
					break;
				}
			}
		 }
		 return $table_defined;
	 }

     private function convertColumnNamesToProperties(array $column_names = array())
	 {
		 $properties = array();
		 if(is_array($column_names) && !empty($column_names))
		 {
			 foreach($column_names as $c)
		     {
				 $prop = "$".$c;
				 array_push($properties, $prop);
		     }
		 }
		 return $properties;
	 }
}
?>

