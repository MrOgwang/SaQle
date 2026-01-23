<?php
namespace SaQle\Http\Request\Data\Sources\Managers\Types;

use SaQle\Core\Support\BindFrom;
use SaQle\Orm\Entities\Model\Schema\Model;
use SaQle\Http\Request\Execution\TypeInspector;
use InvalidArgumentException;
use Exception;
use stdClass;
use JsonException;
use ReflectionClass;

class InputDataSourceManager extends DataSourceManager{
	 public function __construct(BindFrom $from, ...$kwargs){
	 	 parent::__construct($from, ...$kwargs);
	 }

	 /**
	 * Extract an embedded model value from request data.
	 *
	 * @param array  $data      request->data
	 * @param string $key       parameter / field name (e.g. "user")
	 * @param string $class_name  Fully-qualified model class
	 *
	 * @return Model|array|null
	 */
	 private function extract_embedded_model(array $data, string $key, string $class_name){
	     if (!array_key_exists($key, $data)) {
	        return null;
	     }

	     $raw = $data[$key] ?? null;

	     return $this->normalize_embedded_value($raw, $class_name);
	 }

	 private function normalize_embedded_value(mixed $value, string $class_name): Model | array | null {
	 	 //0. Value is null
	 	 if(!$value){
	 	 	return $value;
	 	 }

	     //1. Already a model instance
	     if($value instanceof $class_name){
	         return $value;
	     }

	     //2. stdClass → array
	     if($value instanceof stdClass){
	         return $this->unwrap_envelope((array)$value);
	     }

	     //3. Native array
	     if (is_array($value)) {
	         return $this->unwrap_envelope($value);
	     }

	     //4. String (maybe JSON, maybe reference)
	     if (is_string($value)) {
	         $string = trim($value);

	         //Empty string = not embedded
	         if ($string === '') {
	             return null;
	         }

	         //JSON string
	         if(looks_like_json($string)){
	             $decoded = $this->decode_json_safely($string);
	             return $this->unwrap_envelope($decoded);
	         }

	         //URL-encoded JSON
	         $urldecoded = urldecode($string);
	         if($urldecoded !== $string && $this->looks_like_json($urldecoded)){
	             $decoded = $this->decode_json_safely($urldecoded);
	             return $this->unwrap_envelope($decoded);
	         }

	         //Otherwise: scalar reference (ID, token, etc.)
	         return null;
	     }

	     //5. Scalars (int, float, bool) → reference
	     if(is_scalar($value)){
	         return null;
	     }

	     //6. Anything else is invalid
	     throw new InvalidArgumentException('Unsupported embedded model value of type '.gettype($value));
	 }

	 private function unwrap_envelope(array $data): array {
         //Common envelope keys (order matters)
	     foreach(['_data', 'data', 'payload', 'attributes'] as $key){
	         if(array_key_exists($key, $data) && is_array($data[$key]) && count($data) === 1){
	             return $data[$key];
	         }
	     }

	     return $data;
	 }

	 private function looks_like_json(string $value): bool {
         $value = ltrim($value);

          return (str_starts_with($value, '{') || str_starts_with($value, '['));
     }

     private function decode_json_safely(string $json): array {
	     try{
	         $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	     }catch(JsonException $e){
	         throw new InvalidArgumentException('Invalid JSON provided for embedded model', 0, $e);
	     }

	     if(!is_array($decoded)){
	         throw new InvalidArgumentException('Decoded JSON is not an object or array');
	     }

	     return $decoded;
	 }

	 private function construct_model(string $class_name, mixed $object_data, bool $optional){
	 	 if(!$object_data && $optional){
	 	 	 return null;
	 	 }

	 	 if(!$object_data && !$optional){
	 	 	 throw new InvalidArgumentException("Cannot create object of type: {$class_name}");
	 	 }

	 	 if($object_data instanceof $class_name){
	 	 	 return $object_data;
         }

		 if(is_array($object_data)){
		 	 try{
		 	 	 return new $class_name(...$object_data);
		 	 }catch(Exception $e){
		 	 	 if($optional){
	 	 	         return null;
	 	         }

	 	         throw new InvalidArgumentException("Cannot create object of type: {$class_name} for reason: ".$e->getMessage());
		 	 }
		 }
	 }

	 private function get_flat_keys(string $class_name) : array {
 	 	 $model_fields = $class_name::state()->meta->defined_field_names;
 	 	 $model_columns = $class_name::state()->meta->column_names;
 	 	 $flipped_model_columns = array_flip($model_columns);
 	 	 $object = [];

 	 	 foreach($model_fields as $f){
 	 	 	 $field_name  = '';
 	 	 	 $column_name = '';
 	 	 	 if(array_key_exists($f, $model_columns)){
 	 	 	 	 $field_name  = $f;
 	 	 	     $column_name = $model_columns[$f];
 	 	 	 }elseif(array_key_exists($f, $flipped_model_columns)){
 	 	 	 	 $field_name  = $flipped_model_columns[$f];
 	 	 	     $column_name = $f;
 	 	 	 }

 	 	 	 if($field_name && $column_name){
 	 	 	     $pv = $this->request->data->get($field_name, $this->request->data->get($column_name));
	 	 	 	 if($pv){
	 	 	 	 	 $object[$f] = $pv;
	 	 	 	 }
 	 	 	 }
 	 	 }

 	 	 return $object;
	 }

	 /**
	 * Decide whether request data represents an embedded or flat model input.
	 *
	 * @param array  $data        request->data
	 * @param string $param_name  controller parameter name (e.g. "user")
	 * @param string $class_name   fully-qualified model class
	 *
	 * @return bool true = embedded, false = flat
	 */
	 private function is_embedded_model_input(array $data, string $param_name, string $class_name): bool {
	 	
	     //1.Parameter-name keyed embedding (highest signal)
	     if (array_key_exists($param_name, $data)){
	         return $this->value_represents_object($data[$param_name]);
	     }

	     //2.Type-name keyed embedding (User => [...])
	     $short_name = (new ReflectionClass($class_name))->getShortName();
	     if (array_key_exists($short_name, $data)) {
	         return $this->value_represents_object($data[$short_name]);
	     }

	     //3.Otherwise: flat
	     return false;
	 }

	 /**
	 * Does this value represent an embedded object?
	 */
	 private function value_represents_object(mixed $value): bool{
	     //Already an object or model
	     if(is_object($value)){
	         return true;
	     }

	     //Native array
	     if(is_array($value)){
	         return true;
	     }

	     //JSON string
	     if(is_string($value)){
	         $trimmed = ltrim($value);
	         if($trimmed === ''){
	             return false;
	         }

	         if($trimmed[0] === '{' || $trimmed[0] === '['){
	             json_decode($trimmed);
	             return json_last_error() === JSON_ERROR_NONE;
	         }
	     }

	     return false;
	 }


	 public function get_value() : mixed {

         //is this a simple type i.e int, string, float, array etc
	 	 if(TypeInspector::is_simple_type($this->type)){
             return $this->optional ? 
             $this->request->data->get($this->from->key, $this->default) : 
             $this->request->data->get_or_fail($this->from->key);
         }

         $class_name = TypeInspector::get_class_name($this->type);

         if($class_name && !is_subclass_of($class_name, Model::class)){
             throw new Exception("Cannot bind data of type: {$class_name} from the input! Bind from container instead!");
         }

 	 	 /**
 	 	  * This is a model:
 	 	  * 
 	 	  * 1. if embedded is set to true, the whole model will simply be extracted from a key whose name is pointed by the key and json decoded into
 	 	  * an associative array. This array will be used to instantiate the model object and returned.
 	 	  * 
 	 	  * 2. If not embedded, the field values will be hustled from the incoming data and used to construct the model, which will be returned.
 	 	  *
 	 	  * Also the model will run its own validation on instantiation
 	 	  * */
 	 	 $embedded = !is_null($this->from->embedded) ? 
 	 	 $this->from->embedded : $this->is_embedded_model_input($this->request->data->get_all(), $this->from->key, $class_name);

 	 	 if($embedded){
 	 	 	 $model = $this->extract_embedded_model($this->request->data->get_all(), $this->from->key, $class_name);
	 	     return $this->construct_model($class_name, $model, $this->optional);
 	 	 }

 	 	 $flat_keys = $this->get_flat_keys($class_name);
	 	 return $this->construct_model($class_name, $flat_keys, $this->optional);
	 }
}
