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
	 static protected array $_pre_insert      = [];
     static protected array $_pre_insert_all  = [];
     static protected array $_post_insert     = [];
     static protected array $_post_insert_all = [];

     static protected array $_pre_update      = [];
     static protected array $_pre_update_all  = [];
     static protected array $_post_update     = [];
     static protected array $_post_update_all = [];

     static protected array $_pre_delete      = [];
     static protected array $_pre_delete_all  = [];
     static protected array $_post_delete     = [];
     static protected array $_post_delete_all = [];

     static protected array $_pre_select      = [];
     static protected array $_pre_select_all  = [];
     static protected array $_post_select     = [];
     static protected array $_post_select_all = [];

     static public function before_insert(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];
         
         if(!$modelclass){
             self::$_pre_insert_all = $observerclass;
         }else{
             self::$_pre_insert[$modelclass] = $observerclass;
         }
     }

     static public function after_insert(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_post_insert_all = $observerclass;
         }else{
             self::$_post_insert[$modelclass] = $observerclass;
         }
     }

     static public function before_update(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_pre_update_all = $observerclass;
         }else{
             self::$_pre_update[$modelclass] = $observerclass;
         }
     }

     static public function after_update(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_post_update_all = $observerclass;
         }else{
             self::$_post_update[$modelclass] = $observerclass;
         }
     }

     static public function before_delete(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_pre_delete_all = $observerclass;
         }else{
             self::$_pre_delete[$modelclass] = $observerclass;
         }
     }

     static public function after_delete(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_post_delete_all = $observerclass;
         }else{
             self::$_post_delete[$modelclass] = $observerclass;
         }
     }

     static public function before_select(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_pre_select_all = $observerclass;
         }else{
             self::$_pre_select[$modelclass] = $observerclass;
         }
     }

     static public function after_select(array | string $observerclass, ?string $modelclass = null){
         $observerclass = is_array($observerclass) ? $observerclass : [$observerclass];

         if(!$modelclass){
             self::$_post_select_all = $observerclass;
         }else{
             self::$_post_select[$modelclass] = $observerclass;
         }
     }

     static public function get_model_observers(string $when, string $op, ?string $modelclass = null){
         switch($op){
             case 'select':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_select,
                    'after',  'post' => self::$_post_select,
                 };
             break;
             case 'delete':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_delete,
                    'after',  'post' => self::$_post_delete,
                 };
             break;
             case 'insert':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_insert,
                    'after',  'post' => self::$_post_insert,
                 };
             break;
             case 'update':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_update,
                    'after',  'post' => self::$_post_update,
                 };
             break;
             default:
                 $observers = [];
             break;
         }

         return $observers[$modelclass] ?? [];
     }

     static public function get_shared_observers(string $when, string $op){
         switch($op){
             case 'select':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_select_all,
                    'after',  'post' => self::$_post_select_all,
                 };
             break;
             case 'delete':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_delete_all,
                    'after',  'post' => self::$_post_delete_all,
                 };
             break;
             case 'insert':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_insert_all,
                    'after',  'post' => self::$_post_insert_all,
                 };
             break;
             case 'update':
                 $observers = match($when){
                    'before', 'pre'  => self::$_pre_update_all,
                    'after',  'post' => self::$_post_update_all,
                 };
             break;
             default:
                 $observers = [];
             break;
         }

         return $observers;
     }
}

