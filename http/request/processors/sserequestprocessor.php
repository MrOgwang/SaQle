<?php
namespace SaQle\Http\Request\Processors;

class SseRequestProcessor extends RequestProcessor{
	 private function send_headers(){
         while (ob_get_level() > 0){
             ob_end_flush();
         }
         header('Content-Type: text/event-stream');
         header('Cache-Control: no-cache');
         header('Connection: keep-alive');
     }

     private function send_message(mixed $msg, ?string $id = null){
         if ($id !== null) {
            echo "id: $id\n";
         }
         echo "data: ".json_encode($msg)."\n\n";
         @ob_flush();
         flush();
     }

	 public function process(){
         ignore_user_abort(true);
         set_time_limit(0);

         $this->send_headers();

         $last_id = $_SERVER['HTTP_LAST_EVENT_ID'] ?? '';

         //stream for a limited window, e.g. 15 seconds
         $end = time() + 60;

         while(time() < $end && !connection_aborted()){
             [$http_message, $context_from_parent] = $this->get_target_response($this->request->route->target, $this->request->route->action);
             $response = $http_message->data;

             if($response && is_array($response) && isset($response['id'], $response['data']) && $last_id !== $response['id']){
                 $this->send_message($response['data'], $response['id']);
                 $last_id = $response['id'];
             }else{
                 //heartbeat comment prevents client timeouts
                 echo ": heartbeat\n\n";
                 @ob_flush();
                 flush();
             }
             sleep(0.5);
         }

        //after ~15s, exit and let EventSource reconnect
        exit;
     }
}
