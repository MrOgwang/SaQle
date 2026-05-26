<?php
namespace SaQle\Core\Ui\Utils;

class ComponentPropsParser {
     public static function parse(string $attr_string, array $scope = []) : array {

         echo "Attribute string: {$attr_string}\n";
         print_r($scope);
         echo "-----------------------\n";

         $props = [];

         /**
          * Spread props
          * 
          * {...$props}
          * */
         $spread_pattern = "/\{\s*\.\.\.(.+?)\s*\}/";

         preg_match_all($spread_pattern, $attr_string, $spread_matches);

         foreach($spread_matches[1] as $spread_expression){
             $spread_value = ExpressionEvaluator::evaluate(trim($spread_expression), $scope);

             if(is_array($spread_value)){
                 $props = array_merge($props, $spread_value);
             }
         }

         //remove spreads
         $attr_string = preg_replace($spread_pattern, '', $attr_string);

         /**
          * Normal attributes
          * 
          * name='something' or name='$variable'
          * */
         preg_match_all('/([:\w-]+)=["\'](.*?)["\']/', $attr_string, $matches, PREG_SET_ORDER);

         foreach($matches as $match){
             $key = $match[1];
             $value = $match[2];

             /**
              * Bound prop
              * 
              * :prop = ""
              * */
             if(str_starts_with($key, ':')){
                 $key = substr($key, 1);
                 $props[$key] = ExpressionEvaluator::evaluate($value, $scope);

                 continue;
             }

             //literal prop
             $props[$key] = $value;
         }

         return $props;
     }
}