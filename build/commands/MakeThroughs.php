<?php
namespace SaQle\Build\Commands;

use SaQle\Build\Utils\MigrationUtils;
use SaQle\Orm\Entities\Field\Types\ManyToMany;

class MakeThroughs{

      private function has_manytomany_relationship_with(Model $model1, Model $model2){
         $relation = false;
         for($f = 0; $f < count($model1->meta->nav_field_names); $f++){
             $field = $model1->meta->fields[$model1->meta->nav_field_names[$f]];
             if($field->get_relation()->fmodel == $model2::class && $field instanceof ManyToMany){
                $relation = $field;
             }
         }
         return $relation;
      }

      private function write_through_model($primary_model_instance, $foreign_model_instance, $project_root){
         $pnamespace = MigrationUtils::get_class_namespace($primary_model_instance::class);
         $througnamespace = $pnamespace."\\Throughs";
         $fnamespace = MigrationUtils::get_class_namespace($foreign_model_instance::class);
         $classname = MigrationUtils::get_class_name($primary_model_instance::class).MigrationUtils::get_class_name($foreign_model_instance::class);

         $pmodel_name = strtolower(MigrationUtils::get_class_name($primary_model_instance::class));
         $fmodel_name = strtolower(MigrationUtils::get_class_name($foreign_model_instance::class));
         $o_pmodel_name = MigrationUtils::get_class_name($primary_model_instance::class);
         $o_fmodel_name = MigrationUtils::get_class_name($foreign_model_instance::class);
         $pmodel_pk = $primary_model_instance->meta->pk_name;
         $fmodel_pk = $foreign_model_instance->meta->pk_name;

         $template = "<?php\n";
         $template .= "/**\n";
         $template .= "* This is an auto generated file.\n"; 
         $template .= "*\n";
         $template .= "* The code here is designed to work as is, and must not be modified unless you know what you are doing.\n";
         $template .= "*\n";
         $template .= "* If you find ways that the code can be improved to enhance speed, efficiency or memory, be kind enough\n";
         $template .= "* to share with the author at wycliffomondiotieno@gmail.com or +254741142038. The author will not mind a cup\n";
         $template .= "* of coffee either.\n";
         $template .= "*\n";
         $template .= "* Commands to generate file:\n";
         $template .= "* 1. php manage.php make:migrations\n";
         $template .= "* 2. php manage.php make:throughs\n";
         $template .= "* On your terminal, cd into project root and run the above commands\n";
         $template .= "* \n";
         $template .= "* This is a through model for ".$o_pmodel_name." and ".$o_fmodel_name.". A through model is generated behind the scenes\n";
         $template .= "* when a model defines a many to many field with another model.\n";
         $template .= "* */\n\n";
         $template .= "namespace ".$througnamespace.";\n\n";
         $template .= "use SaQle\\Orm\Entities\\Model\\Interfaces\\IThroughModel;\n";
         $template .= "use SaQle\\Orm\Entities\Field\\Types\\{Pk, OneToOne};\n";
         $template .= "use SaQle\\Orm\Entities\\Model\\Schema\\{Model, TableInfo};\n";
         $template .= "use ".$pnamespace."\\".$o_pmodel_name.";\n";
         $template .= "use ".$fnamespace."\\".$o_fmodel_name.";\n";
         /*$template .= "use SaQle\\Core\\Assert\\Assert;\n\n";*/
         $template .= "class {$classname} extends Model implements IThroughModel{\n\n";
         /*$template .= "\tprivate static array $"."include_fields = [\n";
         $template .= "\t\t".$o_pmodel_name."::class => '".$fmodel_name."',\n";
         $template .= "\t\t".$o_fmodel_name."::class => '".$pmodel_name."'\n";
         $template .= "\t];\n\n";*/
         /**
          * Define the constructor
          * */
         $template .= "\tprotected function model_setup(TableInfo $"."meta) : void{\n";
         $template .= "\t\t$"."meta->fields = [\n";
         $template .= "\t\t\t'id' => new Pk(),\n";
         $template .= "\t\t\t'".$pmodel_name."' => new OneToOne(fmodel: ".$o_pmodel_name."::class, pk: '".$pmodel_name."_id', fk: '".$pmodel_pk."', column_name: '".$pmodel_name."_id'),\n";
         $template .= "\t\t\t'".$fmodel_name."' => new OneToOne(fmodel: ".$o_fmodel_name."::class, pk: '".$fmodel_name."_id', fk: '".$fmodel_pk."', column_name: '".$fmodel_name."_id')\n";
         $template .= "\t\t];\n\n";
         $template .= "\t\t$"."meta->unique_fields   = ['".$pmodel_name."', '".$fmodel_name."'];\n";
         $template .= "\t\t$"."meta->unique_together = true;\n";
         $template .= "\t}\n\n";

         $template .= "}\n";

         $path = $this->get_path_from_namespace($througnamespace, $project_root);
         if(!file_exists($path)){
            mkdir($path, 0755);
         }
         $filename = $path."/".strtolower($classname).".php";

         file_put_contents($filename, $template);
         return $througnamespace."\\".$classname;
      }

      private function extract_through_models($models, $project_root, &$manytomany_throughs){
         $through_models = [];
         foreach($models as $n => $m){
             if(!MigrationUtils::is_model_defined($m, $project_root))
                 continue;

             $mi = $m::state();
             $mfields = $mi->meta->fields;
             foreach($mfields as $mfn => $mfv){
                 if($mfv instanceof ManyToMany){
                     echo "Generating throughs for {$mfn} in {$n}!\n";
                     $relation = $mfv->get_relation();
                     if($relation->through)
                         continue;
                    
                     //get the foreign model.
                     $fmodel = $relation->fmodel;
                      //1. Foreign key model must have a ManyToMany field pointing to current table also defined on it.
                      //2. Foreign key model must also be defined for the through table to be generated
                      //3. The name of the through table will be generated by combining the two class names names.
                     if(!MigrationUtils::is_model_defined($fmodel, $project_root))
                         continue;

                     echo "The foreign model {$fmodel} is defined\n";
                     $fmodel_instance = $fmodel::state();

                     if($this->has_manytomany_relationship_with($fmodel_instance, $mi) === false)
                         continue;

                     echo "The foreign model {$fmodel} has a relationship with primary model: {$m}\n";
                     $first_pointer = strtolower(MigrationUtils::get_class_name($mi::class).MigrationUtils::get_class_name($fmodel));
                     $other_pointer = strtolower(MigrationUtils::get_class_name($fmodel).MigrationUtils::get_class_name($mi::class));

                     if(in_array($first_pointer, $manytomany_throughs) || in_array($other_pointer, $manytomany_throughs))
                         continue;

                     echo "A through field has not been generated for current run!\n";
                     /*$tm = $this->write_through_model($mi, $fmodel_instance, $project_root);
                     $through_models[$first_pointer] = $tm;*/

                     array_push($manytomany_throughs, $first_pointer);
                     array_push($manytomany_throughs, $other_pointer);
                 }
             }
         }
         return $through_models;
      }

      private function make_throughs(string $project_root, $app_name = null, $db_context = null){
         $context_classes = MigrationUtils::get_context_classes($db_context);
         $manytomany_throughs = [];
         
         foreach($context_classes as $ctx){
             $models = new $ctx()->get_permanent_models();
             $this->extract_through_models($models, $project_root, $manytomany_throughs);
         }
      }

      public function execute(string $project_root, $app_name = null, $db_context = null){
           $this->make_throughs($project_root, $app_name, $db_context);
      }
}
