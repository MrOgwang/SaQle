<?php
namespace SaQle\Commons;
trait PolygonUtils{
	 public static function point_in_polygon($point, $polygon, $point_on_vertex = true){
		 // Transform string coordinates into arrays with x and y values
         $point = (new class { use \SaQle\Commons\Commons; })::point_string_to_coordinates($point);
         $vertices = array(); 
         foreach($polygon as $vertex){
            $vertices[] = (new class { use \SaQles\Commons\Commons; })::point_string_to_coordinates($vertex); 
         }
         //Check if the point sits exactly on a vertex
         if($point_on_vertex == true && (new class { use \SaQle\Commons\Commons; })::point_on_vertex($point, $vertices) == true){
             return "vertex";
         }
		 //Check if the point is inside the polygon or on the boundary
         $intersections = 0; 
         $vertices_count = count($vertices);
         for($i = 1; $i < $vertices_count; $i++){
             $vertex1 = $vertices[$i-1]; 
             $vertex2 = $vertices[$i];
             if($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])){ // Check if point is on an horizontal polygon boundary
                return "boundary";
             }
             if($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']){ 
                 $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                 if($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                 }
                 if($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters){
                    $intersections++; 
                 }
             } 
         } 
         //If the number of edges we passed through is odd, then it's in the polygon. 
		 return $intersections % 2 != 0 ? "inside" : "outside";
	 }
	 public static function point_on_vertex($point, $vertices){
         foreach($vertices as $vertex){
             if($point == $vertex){
                 return true;
             }
         }
     }
     public static function point_string_to_coordinates($point_string){
        $coordinates = explode(",", $point_string);
        return ["x" => (float)$coordinates[0], "y" => (float)$coordinates[1]];
     }
}
