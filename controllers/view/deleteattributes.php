<?php
namespace SaQle\Controllers\View;
use SaQle\FeedBack\FeedBack;

class DeleteAttributes{
	 /**
      * When the form is submitted and the request data object contains this property, thats a signal to delete this object
      * @var string
      * */
     public string $delete_property  = "object_delete_signal";
     public bool   $deletable        = false;
     public array  $observer_classes = [];
     public string $success_message  = "Delete operation completed successfully!";
     public bool   $mark_delete      = false;

     public function process(ViewController $controller) : array{
         $request     = $controller->get_request();
         $message     = "";
         if($request->data->get($this->delete_property, '')){
             try{
                 $context = $controller->get_dbcontext();
                 //attach all observers.
                 foreach($this->observer_classes as $observer){
                    $observer_instance = new $observer($controller);
                 }
                 $data_mapping = $controller->get_data_mapping();
                 //print_r($data_mapping);
                 //print_r($request->data);
                 foreach($data_mapping as $complex_table_name => $rows){
                    [$table_name, $table_aliase] = explode(":", $complex_table_name);
                    $modelmanager                = $context->get($table_name);
                    $primary_key_name            = $rows[0]->primary_key_name;
                    $items_to_delete             = [];
                    //delete(bool $permanently = false)
                    foreach($rows as $row_object){
                        //only deal with row_object marked for deletion.
                        if( ($this->mark_delete && $row_object->action == "delete") || !$this->mark_delete){
                            $items_to_delete[] = $row_object->primary_key_value;
                        }
                    }
                    if($items_to_delete){
                        $deleted = $modelmanager->where($primary_key_name."__in", $items_to_delete)->delete(permanently: true);
                    }
                 }

                 //notify the observers
                 $controller->get_feedback()->set(status: FeedBack::SUCCESS);
                 $controller->notify();

                 $_SESSION['viewcontroller_message'] = "
                 <div style='margin-bottom: 20px;' class='system-info system-info-success'>
                     {$this->success_message}
                 </div>
                 ";

                 $controller->reload();

             }catch(\Exception $e){
                $message = "
                <div style='margin-bottom: 20px;' class='system-info system-info-danger'>
                     {$e}
                </div>
                ";
             }
         }
         return ['message' => $message];
     }
}
?>