<?php
namespace SaQle\Core\Observable;

trait ConcreteObservable {
	 protected $observers;

	 protected $obsmethods;

	 public function __construct(){
		 $this->observers = [];
		 $this->obsmethods = [];
	 }

	 /**
     * Build args + args_meta from an associative array
     *
     * @param array $named_args  Associative array ['name' => value, ...]
     * @param array $order       Optional: explicit order of keys for $args
     * @return array             [args, args_meta]
     */
     public static function build_args(array $named_args, array $order = []): array {
         $args = [];
         $args_meta = [];

         //Respect explicit order if given, else use array_keys
         $keys = $order ?: array_keys($named_args);

         foreach ($keys as $key) {
             if (!array_key_exists($key, $named_args)) {
                 continue; // skip if not in array
             }

             $value = $named_args[$key];
             $args[] = $value;

             $args_meta[] = [
                 'name'  => $key,
                 'type'  => is_object($value) ? get_class($value) : gettype($value),
                 'value' => $value
             ];
         }

         return [$args, $args_meta];
     }


	 public function attach($observer, string $method = ""){
         $this->observers[] = $observer;
         $this->obsmethods[] = $method;
     }

     public function attach_all(array $observers){
     	 foreach($observers as $obs){

     	 	 if(!is_string($obs)){
     	 	 	 $this->attach($obs);
     	 	 	 continue;
     	 	 }

     	 	 $obsclass = $obs;
     	 	 $obsmethod = "handle";
     	 	 if(str_contains($obs, "@")){
     	 	 	 $obsprops = explode("@", $obs);
     	 	 	 $obsclass = $obsprops[0];
     	 	     $obsmethod = $obsprops[1];
     	 	 }

     	 	 $observer = new $obsclass();
     	 	 $this->attach($observer, $obsmethod);
     	 }
     }

     public function detach($observer){
         $this->observers = array_filter($this->observers,  function($a) use ($observer){return (!($a === $observer));});
     }

     public function detach_all(){
         foreach($this->observers as $obs){
         	 $this->detach($obs);
         }
	 }

	 public function notify(array $args_meta = [], array $raw_args = []){
	     foreach($this->observers as $obs_index => $obs){
	     	 $method = $this->obsmethods[$obs_index] ?: 'handle';
	         $ref = new \ReflectionMethod($obs, $method);

	         $resolved_args = [];
	         foreach ($ref->getParameters() as $param){
	             $name = $param->getName();
	             $type = $param->getType()?->getName();
	             $match = null;

	             // 1. Match by name
	             if (!empty($args_meta)) {
	                foreach ($args_meta as $meta) {
	                    if ($meta['name'] === $name) {
	                        $match = $meta['value'];
	                        break;
	                    }
	                }
	            }

	            // 2. Match by type
	            if ($match === null && $type) {
	                if (!empty($args_meta)) {
	                    foreach ($args_meta as $meta) {
	                        if ($meta['type'] === $type || ($meta['value'] && is_a($meta['value'], $type))) {
	                            $match = $meta['value'];
	                            break;
	                        }
	                    }
	                }

	                // Allow matching the result for AFTER observers
	                if ($match === null && isset($context['result']) && is_a($context['result'], $type)) {
	                    $match = $context['result'];
	                }
	            }

	            // 3. Fallback: result by name
	            if ($match === null && $name === 'result' && isset($context['result'])) {
	                 $match = $context['result'];
	            }

	            // 4. Fallback to null/default
	            if ($match === null) {
	                $match = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
	            }

	            $resolved_args[] = $match;
	         }

	         $ref->invokeArgs($obs, $resolved_args);
	     }
	 }

	 public function quick_notify(array $observers, mixed $data = null, array $args = [], array $args_meta = []){
	 	 $this->attach_all($observers);
	 	 $this->notify($args_meta, $args);
	 	 $this->detach_all();
	 }
}
