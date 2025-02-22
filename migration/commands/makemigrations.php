<?php
namespace SaQle\Migration\Commands;

use SaQle\Migration\Managers\Interfaces\IMigrationManager;
use SaQle\Migration\Tracker\MigrationTracker;
use SaQle\Commons\FileUtils;
use SaQle\Migration\Models\Migration;

class MakeMigrations{
     use FileUtils;
     public function __construct(private IMigrationManager $manager){

     }

     private function get_model_operations($snapshot){
         $up = "";
         $down = "";
         $touched = "";
         foreach($snapshot as $sk => $sv){
             $added_models = $sv['tables'][0];
             $removed_models = $sv['tables'][1];
             $added_columns = $sv['columns'][0] ?? [];
             $removed_columns = $sv['columns'][1] ?? [];
             $up .= "\t\t\t'".$sk."' => [\n";
             $down .= "\t\t\t'".$sk."' => [\n";
             foreach($added_models as $an => $am){
                 $up .= "\t\t\t\t['action' => 'create_table', 'params' => ['name' => '".$an."', 'model' => '".$am."']],\n";
                 $down .= "\t\t\t\t['action' => 'drop_table', 'params' => ['name' => '".$an."', 'model' => '".$am."']],\n";
             }
             foreach($added_columns as $ac => $acv){
                 $columns_def = "";
                 foreach($acv['columns'] as $acdef_key => $acdef_val){
                    $columns_def .= "\t\t\t\t\t\t'".$acdef_key."' => '".$acdef_val."',\n";
                 }
                 $up .= "\t\t\t\t['action' => 'add_columns', 'params' => ['name' => '".$acv['name']."', 'model' => '".$acv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
                 $down .= "\t\t\t\t['action' => 'drop_columns', 'params' => ['name' => '".$acv['name']."', 'model' => '".$acv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
             }
             foreach($removed_models as $rn => $rm){
                 $up .= "\t\t\t\t['action' => 'drop_table', 'params' => ['name' => '".$rn."', 'model' => '".$rm."']],\n";
                 $down .= "\t\t\t\t['action' => 'create_table', 'params' => ['name' => '".$rn."', 'model' => '".$rm."']],\n";
             }
             foreach($removed_columns as $rc => $rcv){
                 $columns_def = "";
                 foreach($rcv['columns'] as $rcdef_key => $rcdef_val){
                    $columns_def .= "\t\t\t\t\t\t'".$rcdef_key."' => '".$rcdef_val."',\n";
                 }
                 $up .= "\t\t\t\t['action' => 'drop_columns', 'params' => ['name' => '".$rcv['name']."', 'model' => '".$rcv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
                 $down .= "\t\t\t\t['action' => 'add_columns', 'params' => ['name' => '".$rcv['name']."', 'model' => '".$rcv['model']."', 'columns' => [\n".$columns_def."\t\t\t\t]]],\n";
             }
             $up .= "\t\t\t],\n";
             $down .= "\t\t\t],\n";
             $touched = "\t\t\t'".$sk."',\n";
         }
         return [$up, $down, $touched];
     }

     public function execute($migration_name, $project_root, $app_name = null, $db_context = null){
         $timestamp  = date('YmdHis');
         $class_name = 'Migration_' . $timestamp . '_' . $migration_name;
         $migrations_folder = $project_root."/migrations";
         $migration_filename = $migrations_folder."/".$class_name.".php";
         $migration_tracker_filename = $migrations_folder."/migrationstracker.bin";

         $tracker = $this->unserialize_from_file($migration_tracker_filename);
         if(!$tracker){
             $tracker = new MigrationTracker();
         }

         //check that the latest migration file has been migrated
         $current_migration_files = $tracker->get_migration_files();
         if($current_migration_files && $current_migration_files[ count($current_migration_files) - 1 ]->is_migrated === false){
             $fn = $current_migration_files[ count($current_migration_files) - 1 ]->file;
             echo "You have a pending migration file [$fn] that should be migrated first!\n";
             echo "Run command: php manage.php migrate\n";
             return;
         }

         echo "Making {$migration_name} migrations now!\n";
         $snapshot = $this->manager->get_context_snapshot(...[
            'project_root'   => $project_root, 
            'app_name'       => $app_name, 
            'db_context'     => $db_context,
            'timestamp'      => $timestamp,
            'migration_name' => $migration_name,
            'tracker'        => $tracker
         ]);
         $generated_throughs = $snapshot['generated_throughs'];
         unset($snapshot['generated_throughs']);


         echo "Making {$migration_name} migrations now!\n";
         [$up_models, $down_models, $touched_contexts] = $this->get_model_operations($snapshot);
         
         $template = "<?php\n";
         $template .= "use SaQle\\Migration\\Base\\BaseMigration;\n\n";
         $template .= "class {$class_name} extends BaseMigration{\n";
         
         //Construct the touched_contexts method
         $template .= "\tpublic function touched_contexts(){\n";
         $template .= "\t\treturn [\n";
         $template .= $touched_contexts;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the up method.
         $template .= "\tpublic function up(){\n";
         $template .= "\t\treturn [\n";
         $template .= $up_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n\n";
         
         //Construct the down method
         $template .= "\tpublic function down(){\n";
         $template .= "\t\treturn [\n";
         $template .= $down_models;
         $template .= "\t\t];\n";
         $template .= "\t}\n";
         $template .= "}\n";

         //create migrations folder
         if(!file_exists($migrations_folder)){
             mkdir($migrations_folder);
         }

         if(file_put_contents($migration_filename, $template) !== false){
             echo "Migration created: {$migration_filename}\n";

             $tracker->add_migration((Object)['file' => $class_name.".php", 'is_migrated' => false]);
             $tracker->set_through_models($generated_throughs);
             $this->serialize_to_file($migration_tracker_filename, $tracker);
         }
     }
}
