<?php
/**
 * This file is part of SaQle framework.
 * 
 * (c) 2018 SaQle
 * 
 * For the full copyright and license information, please view the LICENSE file
 * that was ditributed with the source code
 * */

/**
 * The model observer class is used to add observer classes to models when initializing the app
 * 
 * This class is used together with the ModelObserversProvider.
 * 
 * @pacakge SaQle
 * @author  Wycliffe Omondi Otieno <wycliffomondiotieno@gmail.com><+254741142038>
 * */
namespace SaQle\Orm\Entities\Model\Observer;

class ModelObserver {
	 static protected array $_pre_insert  = [];
     static protected array $_post_insert = [];
     static protected array $_pre_update  = [];
     static protected array $_post_update = [];
     static protected array $_pre_delete  = [];
     static protected array $_post_delete = [];
     static protected array $_pre_select  = [];
     static protected array $_post_select = [];

     static public function before_insert(string $modelclass, string $observerclass){
         self::$_pre_insert[$modelclass] = $observerclass;
     }

     static public function after_insert(string $modelclass, string $observerclass){
         self::$_post_insert[$modelclass] = $observerclass;
     }

     static public function before_update(string $modelclass, string $observerclass){
         self::$_pre_update[$modelclass] = $observerclass;
     }

     static public function after_update(string $modelclass, string $observerclass){
         self::$_post_update[$modelclass] = $observerclass;
     }

     static public function before_delete(string $modelclass, string $observerclass){
         self::$_pre_delete[$modelclass] = $observerclass;
     }

     static public function after_delete(string $modelclass, string $observerclass){
         self::$_post_delete[$modelclass] = $observerclass;
     }

     static public function before_select(string $modelclass, string $observerclass){
         self::$_pre_select[$modelclass] = $observerclass;
     }

     static public function after_select(string $modelclass, string $observerclass){
         self::$_post_select[$modelclass] = $observerclass;
     }

     static public function get_observers(string $when, string $op, string $modelclass){
         switch($op){
             case 'select':
                 return match($when){
                    'before', 'pre'  => self::$_pre_select,
                    'after',  'post' => self::$_post_select,
                 }[$modelclass];
             break;
             case 'delete':
                 return match($when){
                    'before', 'pre'  => self::$_pre_delete,
                    'after',  'post' => self::$_post_delete,
                 }[$modelclass];
             break;
             case 'insert':
                 return match($when){
                    'before', 'pre'  => self::$_pre_insert,
                    'after',  'post' => self::$_post_insert,
                 }[$modelclass];
             break;
             case 'update':
                 return match($when){
                    'before', 'pre'  => self::$_pre_update,
                    'after',  'post' => self::$_post_update,
                 }[$modelclass];
             break;
         }
     }
}

?>