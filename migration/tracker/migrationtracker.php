<?php
namespace SaQle\Migration\Tracker;

class MigrationTracker{

    /**
     * A list of migration files ordered from earliest to latest. Migration files are executed in this order.
     * My tests have indicated that I cannot rely on the order that migration files were saved to disk is. 
     * 
     * This is an array of key => value objects, each object has a file name and a bool indicating whether the file has been migrated
     * */
	private array $migration_files = [];

    public function add_migration($migration){
    	 $this->migration_files[] = $migration;
    }

    public function get_migration_files(){
        return $this->migration_files;
    }

    public function set_migrated(array $files){
         foreach($this->migration_files as $f){
             if(in_array($f->file, $files)){
                 $f->is_migrated = true;
             }
         }
    }
}
?>