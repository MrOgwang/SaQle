<?php
$GLOBALS["configurations"] = parse_ini_file("../../../config/config.ini");
require_once $_SERVER['DOCUMENT_ROOT'] ."/config/config.php";
require_once REGISTRY ."/registry.php";
session_start();
session_write_close();

//disable default disconnect:
ignore_user_abort(true);

//set headers for the stream:
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

//include required files:

require_once CHAT_M."/chat_m.php";

echo ":".str_repeat(" ", 2048)."\n"; //2kb padding for IE
echo "retry: 2000\n";

if( connection_aborted())
{
	 exit;
}
else
{
	 $chat_model = new \Chats\ActiveChat();
	 //get the latest message sent to this user.
	 $latest_chat = $chat_model->retrieveChats(
	     array("message_to", "=", $_SESSION['current_user']->user_id),
	     array("records_per_page"=>1, "current_page_number"=>1),
		 array("columns"=>array("date_added"), "dir"=>"DESC")
	 );
	 $latest_chat = count($latest_chat->data) > 0 ? $latest_chat->data[0] : NULL;
	 if($latest_chat && !in_array($latest_chat->message_id, $_SESSION['incoming_messages']))
	 {
		 if(!isset($_SESSION['incoming_messages'])) $_SESSION['incoming_messages'] = array();
		 array_push($_SESSION['incoming_messages'], $latest_chat->message_id);
		 $data = json_encode($latest_chat);
		 echo "id: " .$latest_chat->message_id."\n";
		 echo "data: ".$data."\n\n";
		 ob_flush();
		 flush();
	 }
	 else
	 {
		 //no need to send data
		 echo ": heartbeat\n\n";
		 ob_flush();
		 flush();
	 }
}
//5 second sleep then carry on
sleep(5);
?>
