<?php

/**
 * Directory configurations
 * */

return [

     /**
      * Blueprints pecify where your files get uploaded to
      * no matter which storage mechanism you choose
      * */ 
     'blueprints' => [
         'users.avatars' => '/users/avatars/{{ user_id }}/'
     ],

 ]
?>