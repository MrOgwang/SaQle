<?php

declare(strict_types=1);

namespace SaQle\Core\Ui\Utils;

final class Label {

	 /**
	  * Take a model field and convert it into a label
	  * that can be used in forms or display panels
	  * */
	 public static function make(string $name){

	 	 //replace snake_case underscores with spaces
         $label = str_replace('_', ' ', $name);

         // split camelCase & PascalCase
         // - FooBar → Foo Bar
         // - fooBar → foo Bar
         // - APIResponse → API Response
         $label = preg_replace(
             '/(?<=\p{Ll})(?=\p{Lu})|(?<=\p{Lu})(?=\p{Lu}\p{Ll})/u',
             ' ',
             $label
         );

         //normalize spacing
         $label = preg_replace('/\s+/', ' ', $label);

         //title case while preserving acronyms
         $label = ucwords(strtolower($label));

         //restore common acronyms
         $label = preg_replace_callback('/\b(Id|Api|Url|Uuid|Ip)\b/', function ($m){
             return strtoupper($m[0]);
         }, $label);

         return trim($label);
	 }
}