<?php

use SaQle\Orm\Entities\Field\Interfaces\IField;
use SaQle\Orm\Entities\Field\Types\{
     VirtualField, VideoField, ImageField, UuidField, UrlField,
     TimeField, TextField, SlugField, PhoneField, PasswordField, JsonField,
     IpAddressField, IntegerField, FloatField, FileField, EmailField,
     DecimalField, DateTimeField, DateField, ChoiceField, CharField, 
     BooleanField, OneToOne, ManyRelation
};

if(!function_exists('many_of')){
     function many_of(string $model, ?string $local_key = null, ?string $foreign_key = null): IField {
         return new ManyRelation(model: $model, local_key: $local_key, foreign_key: $foreign_key);
     }
}

if(!function_exists('one_of')){
     function one_of(string $model, ?string $local_key = null, ?string $foreign_key = null): IField {
         return new OneToOne(related_model: $model, local_key: $local_key, foreign_key: $foreign_key);
     }
}

if(!function_exists('boolean_field')){
     function boolean_field(...$kwargs): IField {
         return new BooleanField(...$kwargs);
     }
}

if(!function_exists('char_field')){
     function char_field(...$kwargs): IField {
         return new CharField(...$kwargs);
     }
}

if(!function_exists('choice_field')){
     function choice_field(...$kwargs): IField {
         return new ChoiceField(...$kwargs);
     }
}

if(!function_exists('date_field')){
     function date_field(...$kwargs): IField {
         return new DateField(...$kwargs);
     }
}

if(!function_exists('datetime_field')){
     function datetime_field(...$kwargs): IField {
         return new DateTimeField(...$kwargs);
     }
}

if(!function_exists('decimal_field')){
     function decimal_field(...$kwargs): IField {
         return new DecimalField(...$kwargs);
     }
}

if(!function_exists('email_field')){
     function email_field(...$kwargs): IField {
         return new EmailField(...$kwargs);
     }
}

if(!function_exists('file_field')){
     function file_field(...$kwargs): IField {
         return new FileField(...$kwargs);
     }
}

if(!function_exists('float_field')){
     function float_field(...$kwargs): IField {
         return new FloatField(...$kwargs);
     }
}

if(!function_exists('integer_field')){
     function integer_field(...$kwargs): IField {
         return new IntegerField(...$kwargs);
     }
}

if(!function_exists('ip_address_field')){
     function ip_address_field(...$kwargs): IField {
         return new IpAddressField(...$kwargs);
     }
}

if(!function_exists('json_field')){
     function json_field(...$kwargs): IField {
         return new JsonField(...$kwargs);
     }
}

if(!function_exists('password_field')){
     function password_field(...$kwargs): IField {
         return new PasswordField(...$kwargs);
     }
}

if(!function_exists('phone_field')){
     function phone_field(...$kwargs): IField {
         return new PhoneField(...$kwargs);
     }
}

if(!function_exists('slug_field')){
     function slug_field(...$kwargs): IField {
         return new SlugField(...$kwargs);
     }
}

if(!function_exists('text_field')){
     function text_field(...$kwargs): IField {
         return new TextField(...$kwargs);
     }
}

if(!function_exists('time_field')){
     function time_field(...$kwargs): IField {
         return new TimeField(...$kwargs);
     }
}

if(!function_exists('url_field')){
     function url_field(...$kwargs): IField {
         return new UrlField(...$kwargs);
     }
}

if(!function_exists('uuid_field')){
     function uuid_field(...$kwargs): IField {
         return new UuidField(...$kwargs);
     }
}

if(!function_exists('image_field')){
     function image_field(...$kwargs): IField {
         return new ImageField(...$kwargs);
     }
}

if(!function_exists('virtual_field')){
     function virtual_field(...$kwargs): IField {
         return new VirtualField(...$kwargs);
     }
}

if(!function_exists('video_field')){
     function video_field(...$kwargs): IField {
         return new VideoField(...$kwargs);
     }
}