<?php
namespace SaQle\Build\Commands;

use Booibo\Models\{Actor, Film, Category, City, Country};

class TestModel {
     private static function test_models(string $project_root){
         cli_log("Testing models!\n");

         /*$actors = Actor::using('sakila')->get()->with(
             ['films.language'],

             //tuning seems to cause a problem!
             /*[
                 'films' => function($q){
                      return $q->limit(1, 5)->order(['length'], 'ASC');
                  }
             ]*
         )->limit(page: 1, records: 5)->all();
         print_r($actors);*/

         $films = Film::using('sakila')->get()->with(['actors', 'language', 'categories'], /*[
            'actors' => function($q){
                      return $q->limit(1, 5)->order(['last_update'], 'ASC');
                  }
         ]*/)->limit(page: 1, records: 5)->all();
         print_r($films);
         /*foreach($films as $index => $flm) {
             $n = $index + 1;
             cli_log("{$n} - {$flm->title}\n");
         }

         /*$categories = Category::using('sakila')->get()->all();
         foreach($categories as $index => $cat) {
             $n = $index + 1;
             cli_log("{$n} - {$cat->name}\n");
         }

         $cities = City::using('sakila')->get()->with(['country'])->all();
         foreach($cities as $index => $city) {
             $n = $index + 1;
             cli_log("{$n} - {$city->country->country}\n");
         }

         $countries = Country::using('sakila')->get()->with(['cities'])->all();
         foreach($cities as $index => $city) {
             $n = $index + 1;
             cli_log("{$n} - {$city->country->country}\n");
         }*/
     }

     static public function execute($project_root){
         self::test_models($project_root);
     }
}
