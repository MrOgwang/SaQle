<?php

/**
 * Queue configurations
 * */

 return [

     'driver' => 'db',

     'default' => 'default',

     //map features to queue names
     'routing' => [
         'mail' => 'emails',
         'notifications' => 'notifications',
         'reports' => 'reports',
         'uploads' => 'uploads',
         'webhooks' => 'webhooks'
     ],

     //worker settings per queue
     'queues' => [
         'emails' => [
             'priority' => 10,
             'timeout' => 60,
             'tries' => 3
         ],
         'notifications' => [
             'priority' => 20,
             'timeout' => 30,
             'tries' => 5
         ],
         'reports' => [
             'priority' => 1,
             'timeout' => 300,
             'tries' => 1
         ]
     ]
];

?>