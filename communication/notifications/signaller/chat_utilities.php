<?php
require_once "utilityFunctions.php";
class ChatUtilities
{
	 use UtilityFunctions;
	 protected $db = ""; //a database reference.

	 public function __construct()
	 {
		 $this->db = $this->connectToDatabase(); //this function is defined in utilityFunctions.php
	 }

   public function addNewChat($chat_from, $chat_to, $chat_message, $chat_file, $contact_type, $room_type)
   {
       $addedChat = new stdClass();
       //date of chat is current date:
       $chat_date = $this->getCurrentDate();
       //time of chat is current time:
       $chat_time = $this->getCurrentTime();
       //get the file link:
       if(is_null($chat_file))
       {
           $chat_file_url = NULL;
       }
       else
       {
           $chat_file_url = NULL;
           $chat_files_path = "user_files/" .strtolower(str_replace(" ", "_", $_SESSION['fullname']))."_".$_SESSION['userid'] ."/chat_files";
           if(!file_exists($chat_files_path))
           {
               mkdir($chat_files_path);
           }
           $chat_file_url = $chat_files_path ."/" .$chat_file['name'];
       }

       $chat_inserted = $this->insertChat($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url, $contact_type, $room_type);
       if($chat_inserted == true)
       {
           $file_saved = move_uploaded_file($chat_file['tmp_name'], $chat_file_url);
           $addedChat = $this->getJustAddedChat($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url);
       }
       return $addedChat;
	 }
	 //add a new chat:
	 private function insertChat($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url, $contact_type, $room_type)
	 {
         $sql = "INSERT INTO chats (chat_date, chat_time, chat_from, chat_to, chat_message, chat_file) VALUES (?, ?, ?, ?, ?, ?)";
         $data = array($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url);
         if($contact_type == "room")
         {
             $sql = "INSERT INTO chats (chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
             $data = array($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url, $contact_type, $chat_to, $room_type);
         }
         $statement = $this->makeStatement($this->db, $sql, $data);
         $addedChat = $this->getJustAddedChat($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url);
         return $addedChat;
	 }

	 private function getJustAddedChat($chat_date, $chat_time, $chat_from, $chat_to, $chat_message, $chat_file_url)
	 {
       $addedChat = new stdClass();
			 $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type,
			                CONCAT_WS(' ', first_name, last_name) AS sender_name, gender, user_photo
			         FROM chats INNER JOIN user_account ON chats.chat_from = user_account.user_id
							 WHERE chat_date = ? AND chat_time = ? AND chat_from = ? AND chat_to = ? AND chat_message = ?";
			 $data = array($chat_date, $chat_time, $chat_from, $chat_to, $chat_message);
			 $statement = $this->makeStatement($this->db, $sql, $data);
       if($statement->rowCount() == 1)
			 {
				   $addedChat = $statement->fetchObject();
			 }
			 return $addedChat;
	 }

   //get latest incoming chat:
	 //the following gets the latest chat message sent to this user:
	 public function getLatestIncomingChat($user_id)
	 {
		   $latestChat = new stdClass();
		   $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file,
			                user_id, CONCAT_WS(' ', first_name, last_name) AS sender_name, gender, user_photo
          		 FROM chats
				       INNER JOIN user_account ON chats.chat_from = user_account.user_id
				       WHERE chat_to = ? ORDER BY chats.chat_id DESC LIMIT 1";
		   $data = array($user_id);
		   $statement = $this->makeStatement($this->db, $sql, $data);
			 if($statement->rowCount() === 1)
			 {
				   $latestChat = $statement->fetchObject();
			 }
		   return $latestChat;
	 }

	 //load the conversation between this user and a given contact:
	 public function getUserConversationWithContact($user_id, $contact_id)
	 {
		   $chats = array();
		   $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type,
				user_id, CONCAT_WS(' ', first_name, last_name) AS sender_name, gender, user_photo
						   FROM chats
						   INNER JOIN user_account ON chats.chat_from = user_account.user_id
						   WHERE (chat_from = ? AND chat_to = ?) OR (chat_from = ? AND chat_to = ?) ORDER BY chats.chat_id ASC";
		   $data = array($user_id, $contact_id, $contact_id, $user_id);
		   $statement = $this->makeStatement($this->db, $sql, $data);
		   while($chat = $statement->fetchObject())
		   {
               if($chat->chat_date == $this->getCurrentDate())
               {
                   $chat->chat_date = "Today";
               }
               array_push($chats, $chat);
		   }
		   return $chats;
	 }
    
    function getChatDetails($chat_id, $requester)
    {
        $chat = new stdClass();
        $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type,
				user_id, CONCAT_WS(' ', first_name, last_name) AS sender_name, gender, user_photo
				FROM chats
				INNER JOIN user_account ON chats.chat_from = user_account.user_id
				WHERE chat_id = ?";
        if($requester == "sender")
        {
            $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type,
				user_id, CONCAT_WS(' ', first_name, last_name) AS receiver_name, gender, user_photo
				FROM chats
				INNER JOIN user_account ON chats.chat_to = user_account.user_id
				WHERE chat_id = ?";
        }
        $data = array($chat_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        if($statement->rowCount() == 1)
        {
            $chat = $statement->fetchObject();
            if($chat->chat_date == $this->getCurrentDate())
            {
                $chat->chat_date = "Today";
            }
        }
        return $chat;
    }
    
    public function getRoomConversations($room_id)
    {
        $chats = array();
        $sql = "SELECT chat_id, chat_date, chat_time, chat_from, chat_to, chat_message, chat_file, chat_type, room_id, room_type,
                user_id, CONCAT_WS(' ', first_name, last_name) AS sender_name, gender, user_photo
                FROM chats
                INNER JOIN user_account ON chats.chat_from = user_account.user_id
                WHERE chat_type = 'room' AND room_id = ? ORDER BY chats.chat_id ASC";
        $data = array($room_id);
        $statement = $this->makeStatement($this->db, $sql, $data);
        while($chat = $statement->fetchObject())
        {
            if($chat->chat_date == $this->getCurrentDate())
            {
                $chat->chat_date = "Today";
            }
            array_push($chats, $chat);
        }
        return $chats;
     }

     public function addLessonChat($sender, $except, $message, $session_id, $lesson_id)
     {
         $date = time();
         $this->insertLessonChat($date, $sender, $except, $message, $session_id, $lesson_id);
         return $this->getJustAddedLessonChat($date, $sender, $message, $lesson_id);
     }
     
     public function insertLessonChat($date, $sender, $except, $message, $session_id, $lesson_id)
     {
         $sql = "INSERT INTO lesson_chats (date, sender, except, message, session_id, lesson_id) VALUES(?, ?, ?, ?, ?, ?)";
         $data = array($date, $sender, $except, $message, $session_id, $lesson_id);
         $statement = $this->makeStatement($this->db, $sql, $data);
         return true;
     }

     private function getJustAddedLessonChat($date, $sender, $message, $lesson_id)
     {
         $chat = new stdClass();
         $sql = "SELECT * FROM lesson_chats WHERE date = ? AND sender = ? AND message = ? AND lesson_id = ?";
         $data = array($date, $sender, $message, $lesson_id);
         $statement = $this->makeStatement($this->db, $sql, $data);
         if($statement->rowCount() === 1)
         {
             $chat = $statement->fetchObject();
         }
         return $chat;
     }

     public function getLessonChats($lesson_id)
     {
         $chats = array();
         $sql = "SELECT * FROM lesson_chats WHERE lesson_id = ? ORDER BY lesson_chats.id ASC";
         $data = array($lesson_id);
         $statement = $this->makeStatement($this->db, $sql, $data);
         while($chat = $statement->fetchObject())
         {
            array_push($chats, $chat);
         }
         return $chats;
     }
}

?>
