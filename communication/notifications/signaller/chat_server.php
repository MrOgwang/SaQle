<?php
//start session and make it read only: this will prevent session locks:
session_start();
//session_write_close();

//disable default disconnect:
ignore_user_abort(true);

//set headers for the stream:
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');

//include required files:
require_once "chat_utilities.php";
require_once "userUtilities.php";

echo ":".str_repeat(" ", 2048)."\n"; //2kb padding for IE
echo "retry: 2000\n";

if( connection_aborted())
{
	 exit;
}
else
{
	   /*
		 the chat server looks for any chat updates and sends them to the relevant users:
		 */
     //create a new chat utility object:
     $chatUtility = new ChatUtilities();
     $userUtility = new UserUtilities();
	   $latestChat = $chatUtility->getLatestIncomingChat($_SESSION['userid']);
	   //check the chat to determine if one was found and if the id is not in the sessions incoming chats array
       //if(true)
	   if( isset($latestChat->chat_id) && !in_array($latestChat->chat_id, $_SESSION['incoming_chats']) )
	   {
           $latestChat->user_photo = $userUtility->getUserDisplayPicture("", $latestChat->gender, $latestChat->user_photo);
			   //add the chat id to sessions incoming chats array:
             array_push($_SESSION['incoming_chats'], $latestChat->chat_id);
		     $data = json_encode($latestChat);
		     echo "id: ".$latestChat->chat_id."\n";
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
//1 second sleep then carry on
sleep(1);
?>
