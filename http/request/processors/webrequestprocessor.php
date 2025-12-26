<?php
namespace SaQle\Http\Request\Processors;

use ReflectionClass;
use SaQle\Views\View;
use SaQle\Commons\StringUtils;
use SaQle\Http\Request\Middleware\CsrfMiddleware;
use SaQle\Controllers\Interfaces\WebController;
use SaQle\Core\FeedBack\ExceptionFeedBack;
use SaQle\Templates\Template;
use SaQle\Auth\Models\GuestUser;

class WebRequestProcessor extends RequestProcessor{
	 use StringUtils;

     private array $components;

     public function __construct(){
     	 $this->components = require_once DOCUMENT_ROOT.CLASS_MAPPINGS_DIR."components.php";
     	 parent::__construct();
     }

     /**
	 * Finds the position of the matching <!--END COMPONENT--> for the start marker
	 * at $startPos (position of '<' of <!--COMPONENT:...-->).
	 * Returns the index of the start of the END marker, or false if none found.
	 */
	 private function find_matching_end_pos(string $html, int $startPos){
	     $startPattern = '/<!--DYNAMIC:[a-zA-Z0-9_\-]+-->/';
	     $endPattern   = '/<!--END DYNAMIC-->/';

	     //find the exact start marker string and its length
	     if(!preg_match($startPattern, $html, $mStart, PREG_OFFSET_CAPTURE, $startPos)){
	         return false;
	     }
	     $startMarker = $mStart[0][0];
	     $cursor = $mStart[0][1] + strlen($startMarker);
	     $depth = 0;

	     while(true){
	         $nextStart = preg_match($startPattern, $html, $mS, PREG_OFFSET_CAPTURE, $cursor) ? $mS[0][1] : false;
	         $nextEnd   = preg_match($endPattern,   $html, $mE, PREG_OFFSET_CAPTURE, $cursor) ? $mE[0][1] : false;

	         if($nextStart === false && $nextEnd === false){
	             //malformed / no closing end
	             return false;
	         }

	         //if nextEnd exists and is earlier than nextStart (or nextStart doesn't exist)
	         if($nextEnd !== false && ($nextStart === false || $nextEnd < $nextStart)){
	             if($depth === 0){
	                 //this END matches our original start
	                 return $nextEnd;
	             }else{
	                 $depth--;
	                 $cursor = $nextEnd + strlen($mE[0][0]);
	             }
	         }else{
	             //we found a nested start before the next end
	             $depth++;
	             $cursor = $nextStart + strlen($mS[0][0]);
	         }
	     }
	 }

	 /**
	 * Extract the current outermost (first) component from $html, replace it with a placeholder,
	 * and return an array with component details or null if none found.
	 *
	 * Modifies $html by reference.
	 *
	 * Return format:
	 *  [
	 *    'name' => 'componentName',
	 *    'full' => '<!--COMPONENT:name--> ... <!--END COMPONENT-->',   // full extracted
	 *    'inner'=> '... inner content ...'                            // between markers
	 *  ]
	 */
	 private function extract_topmost_component_step(string &$html): ?array{
	     $startPattern = '/<!--DYNAMIC:([a-zA-Z0-9_\-]+)-->/';

	     if (!preg_match($startPattern, $html, $m, PREG_OFFSET_CAPTURE)) {
	         return null; // no component found
	     }

	     $name = $m[1][0];
	     $startPos = $m[0][1];
	     $startMarker = $m[0][0];

	     $endPos = $this->find_matching_end_pos($html, $startPos);
	     if ($endPos === false) {
	         // malformed structure: couldn't find matching END
	         return null;
	     }
	     $endMarker = '<!--END DYNAMIC-->';
	     $endFullPos = $endPos + strlen($endMarker);

	     $full = substr($html, $startPos, $endFullPos - $startPos);

	     //extract inner content (between startMarker and endMarker)
	     $innerStart = $startPos + strlen($startMarker);
	     $innerLen = $endPos - $innerStart;
	     $inner = $innerLen > 0 ? substr($html, $innerStart, $innerLen) : '';

	     //Replace the entire component with a placeholder
	     $placeholder = "<!--DYNAMIC:$name-->";
	     $html = substr_replace($html, $placeholder, $startPos, $endFullPos - $startPos);

	     return [
	        'name' => $name,
	        'full' => $full,
	        'inner' => $inner
	     ];
	 }

	 /**
	 * Reinsert a previously extracted/processed component into $html at the placeholder,
	 * but remove the component markers so it looks like the component never existed.
	 *
	 * $processedInner should be the processed inner HTML (it may or may not contain
	 * the component markers — they will be stripped if present).
	 *
	 * Modifies $html by reference.
	 */
	 private function reinsert_and_remove_marker(string &$html, string $name, string $processedInner, string $type = 'DYNAMIC'): void{
	     $placeholder = "<!--$type:$name-->";

	     //If placeholder does not exist, skip reinsertion
	     if(strpos($html, $placeholder) === false){
	         return;
	     }

	     //If processedInner contains full markers, strip them for this component
	     //(strip only the outer markers for this name if present)
	     $startMarkerPattern = '/^\s*<!--'.$type.':'.preg_quote($name, '/').'-->\s*/';
	     $endMarkerPattern = '/\s*<!--END '.$type.'-->\s*$/';

	     //remove the outer start marker if present
	     $processedInner = preg_replace($startMarkerPattern, '', $processedInner, 1);
	     //remove trailing end marker if present
	     $processedInner = preg_replace($endMarkerPattern, '', $processedInner, 1);

	     //Now replace placeholder with the processed (marker-free) content
	     $html = str_replace($placeholder, $processedInner, $html);
	 }

	 /**
	 * Extract only top-level sibling components (depth 0).
	 *
	 * Returns:
	 * [
	 *   'components' => [ 'name' => [ '<!--COMPONENT:name-->...<!--END COMPONENT-->', ... ] ],
	 *   'html'       => 'html with <!--PLACEHOLDER:name--> markers replacing the extracted top-level components'
	 * ]
	 */
	 private function extract_top_level_components(string $html): array{
	    $startRegex = '<!--COMPONENT:([A-Za-z0-9_\-]+)-->';
	    $endRegex   = '<!--END COMPONENT-->';
	    $tokenPattern = "/($startRegex|$endRegex)/";

	    // find all start/end tokens with offsets
	    if (!preg_match_all($tokenPattern, $html, $matches, PREG_OFFSET_CAPTURE)) {
	        return ['components' => [], 'html' => $html];
	    }

	    // Build a list of tokens in order
	    $tokens = [];
	    foreach ($matches[0] as $i => $m) {
	        $tokenText = $m[0];
	        $offset = $m[1];
	        // If it's a start marker, capture the name from matches[1/2] groups.
	        $name = null;
	        // matches grouping layout: full match in $matches[0], captured name in $matches[1] or $matches[2]
	        // But simpler: try to extract name from text.
	        if (preg_match('/^<!--COMPONENT:([A-Za-z0-9_\-]+)-->$/', $tokenText, $nm)) {
	            $tokens[] = ['type' => 'start', 'name' => $nm[1], 'offset' => $offset, 'text' => $tokenText];
	        } else {
	            $tokens[] = ['type' => 'end', 'offset' => $offset, 'text' => $tokenText];
	        }
	    }

	    $stack = [];
	    $topLevelRanges = []; // each item: ['name'=>..., 'start'=>int, 'end'=>int]

	    // Walk tokens in order, manage stack to detect matching pairs.
	    foreach ($tokens as $token) {
	        if ($token['type'] === 'start') {
	            $isTopLevel = (count($stack) === 0);
	            $stack[] = [
	                'name' => $token['name'],
	                'start_pos' => $token['offset'],
	                'start_text_len' => strlen($token['text']),
	                'is_top' => $isTopLevel
	            ];
	        } else { // end token
	            if (empty($stack)) {
	                // unmatched END — skip (malformed); continue
	                continue;
	            }
	            $entry = array_pop($stack);
	            // end position is the offset where <!--END COMPONENT--> starts
	            $endPos = $token['offset'];
	            $endMarkerLen = strlen($token['text']); // length of <!--END COMPONENT-->
	            // If this popped start was a top-level start, record its full range.
	            if ($entry['is_top']) {
	                $start = $entry['start_pos'];
	                $end = $endPos + $endMarkerLen; // exclusive end (position after end marker)
	                $topLevelRanges[] = [
	                    'name' => $entry['name'],
	                    'start' => $start,
	                    'end' => $end
	                ];
	            }
	            // otherwise: nested component closed — ignore (we only want top-level)
	        }
	    }

	    // If no top-level ranges found, return early
	    if (empty($topLevelRanges)) {
	        return ['components' => [], 'html' => $html];
	    }

	    // Build components array (support duplicate top-level names)
	    $components = [];
	    foreach ($topLevelRanges as $r) {
	        $full = substr($html, $r['start'], $r['end'] - $r['start']);
	        $name = $r['name'];
	        if (!array_key_exists($name, $components)) {
	            $components[$name] = [];
	        }
	        $components[$name][] = $full;
	    }

	    // Replace ranges with placeholders in the html.
	    // Do replacements from right-to-left to preserve offsets.
	    usort($topLevelRanges, function($a, $b) {
	        return $b['start'] <=> $a['start'];
	    });

	    $cleanHtml = $html;
	    foreach ($topLevelRanges as $r) {
	        $placeholder = "<!--COMPONENT:{$r['name']}-->";
	        $cleanHtml = substr_replace($cleanHtml, $placeholder, $r['start'], $r['end'] - $r['start']);
	    }

	    return [
	        'components' => $components,
	        'html' => $cleanHtml
	    ];
	 }

	 private function prepare_context(){
	 	 $context = [];

	 	 //add feedback context
         $efb = ExceptionFeedBack::init();
         $context = array_merge($context, $efb->acquire_context());

         //inject global context data
         $context = array_merge($context, Template::init()::get_context());

         //inject csrf token input here
         $token_key = CsrfMiddleware::get_token_key();
         $token     = CsrfMiddleware::get_token();

         $context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

         //inject the user
         $context['session_user'] = $this->request->user ?? new GuestUser();

         return $context;
	 }

     private function parse_component($name, $html){
     	 //echo "Now parsing: $name\n";
     	 if($name === 'timeline'){
     	 	 //echo $html;
     	 }
     	 //remove top most component markers from html
	     $startMarkerPattern = '/^\s*<!--COMPONENT:'.preg_quote($name, '/').'-->\s*/';
	     $endMarkerPattern = '/\s*<!--END COMPONENT-->\s*$/';

	     //remove the outer start marker if present
	     $html = preg_replace($startMarkerPattern, '', $html, 1);
	     //remove trailing end marker if present
	     $html = preg_replace($endMarkerPattern, '', $html, 1);

         //get the controller class
     	 $controller_class = $this->components[$name]['controller'];
     	 
     	 //Make the context available first.
         $context = $this->prepare_context();

     	 if($controller_class && class_exists($controller_class)){
     	 	 $instance = new $controller_class();
     	 	 $controller_method = $instance->get_index();
     	 	 $instance = null;

     	 	 $http_message = $this->get_target_response($controller_class, $controller_method);
         	 $context = array_merge($context, $http_message->data ?? []);
     	 }

     	 //extract blocks here, keeping reference to them
         $block = $this->extract_top_level_components($html);

         //clean template
         $html = $block['html'];

         //with clean template, fill out the context
     	 $view = new View($html, false);
     	 $view->set_context($context);
     	 $html = $view->view();

     	 //at this point, some blocks no longer exist. Parse and re-insert the remaining blocks
     	 $remaining_blocks = $this->find_remaining_components($html);
     	 if(!$remaining_blocks)
     	 	 return $html;

 	 	 foreach($remaining_blocks as $b){
 	 	 	 $block_template = $block['components'][$b][0] ?? '';
 	 	 	 if($block_template){
 	 	 	 	 $block_template = $this->parse_component($b, $block_template);
 	 	 	 	 //reinsert block
 	 	 	 	 $this->reinsert_and_remove_marker($html, $b, $block_template, 'COMPONENT');
 	 	 	 }
 	 	 }

     	 return $html;
     }

     private function find_remaining_components($html){
     	 $pattern = '/<!--COMPONENT:(.*?)-->/s';
         $components = [];

         $html = preg_replace_callback($pattern, function($matches) use (&$components){
             $components[] = trim($matches[1]);
             return "<!--COMPONENT:".trim($matches[1])."-->";
         }, $html);

         return $components;
     }

     private function parse_template(array $trail, array $uitree, string $template, int $component_index = 0){
     	 if(!isset($uitree[$component_index]) || !isset($trail[$component_index])){
     	 	 return $template;
     	 }

         //echo "Component index: $component_index\n";
     	 $current_trail_stop = $trail[$component_index];
     	 $current_tree_stop = $uitree[$component_index];

     	 $controller_class = $current_trail_stop->target;
     	 $controller_method = $current_trail_stop->action;

         //Make the context available first.
         $context = $this->prepare_context();

     	 if(class_exists($controller_class)){
     	 	 $http_message = $this->get_target_response($controller_class, $controller_method);
         	 $context = array_merge($context, $http_message->data ?? []);
     	 }

     	 //extract out dynamic part, keeping a referecnce to them
     	 $ext = $this->extract_topmost_component_step($template);

     	 //extract blocks here, keeping reference to them
         $block = $this->extract_top_level_components($template);

         //clean template
         $template = $block['html'];

         //with clean template, fill out the context
     	 $view = new View($template, false);
     	 $view->set_context($context);
     	 $template = $view->view();

     	 //at this point, some blocks no longer exist. Parse and re-insert the remaining blocks
     	 $remaining_blocks = $this->find_remaining_components($template);
     	 if($remaining_blocks){
     	 	 foreach($remaining_blocks as $b){
     	 	 	 $block_template = $block['components'][$b][0] ?? '';
     	 	 	 if($block_template){
     	 	 	 	 $block_template = $this->parse_component($b, $block_template);
     	 	 	 	 //reinsert block
     	 	 	 	 $this->reinsert_and_remove_marker($template, $b, $block_template, 'COMPONENT');
     	 	 	 }
     	 	 }
     	 }

     	 if($ext){
     	 	 //reinsert the component
     	     $this->reinsert_and_remove_marker($template, $ext['name'], $ext['inner']);
     	 }

         //return parsed template
     	 return $this->parse_template($trail, $uitree, $template, $component_index + 1);
     }

	 public function process(){
	 	 if(str_starts_with($_SERVER['REQUEST_URI'], MEDIA_URL) || str_starts_with($_SERVER['REQUEST_URI'], CRON_URL)){
             //serve media file
	 	 	 $tc = count($this->request->trail);
	 	 	 $this->serve_media($this->request->trail[$tc - 1]->target, $this->request->trail[$tc - 1]->action);
         }else{
         	 //get the request trail
         	 $trail = $this->request->trail;

         	 $page_class = $this->components['page']['controller'];
             $page_instance = new $page_class();

         	 array_unshift($trail, (Object)['target' => $page_class, 'action' => $page_instance->get_index()]);

         	 //get the ui tree
         	 $focused_url = $this->request->route->url."/";
         	 
         	 $mappings_file = DOCUMENT_ROOT.CLASS_MAPPINGS_DIR."routes.php";
         	 $route_mappings = file_exists($mappings_file) ? require_once $mappings_file : [];
         	 $template_path = '';
         	 $uitree = [];

         	 if(array_key_exists($focused_url, $route_mappings)){
         	 	 $uitree = $route_mappings[$focused_url]['uitree'];
         	 	 $template_path = $route_mappings[$focused_url]['template_path'];
         	 }

         	 if($template_path && file_exists($template_path) && $uitree){
         	 	 $html = $this->parse_template($trail, $uitree, file_get_contents($template_path), 0);
         	 	 echo $html;
         	 }

         	 /*/gather all the context data in one place
         	 $context = [];

         	 foreach($trail as $t){
         	 	 if(class_exists($t->target)){
         	 	 	 //$http_message = $this->get_target_response($t->target, $t->action);
         	 	 	 //$context = array_merge($context, $http_message->data);
         	 	 	 //$context['http_response_code'] = $http_message->code;
	 	             //$context['http_response_message'] = $http_message->message;
         	 	 }
         	 }

         	 //call default and block controllers to fill in their context data as you find the comipled template for this route
         	 
         	 //add feedback context
	         $efb = ExceptionFeedBack::init();
	         $context = array_merge($context, $efb->acquire_context());

	         //inject global context data
	         $context = array_merge($context, Template::init()::get_context());

             //inject csrf token input here
             $token_key = CsrfMiddleware::get_token_key();
             $token     = CsrfMiddleware::get_token();

             $context[$token_key] = "<input type='hidden' id='".$token_key."' name='".$token_key."' value='".$token."'>";

             //inject the user
             $context['session_user'] = $this->request->user ?? new GuestUser();

             if($template_path){
             	 $page = new View($template_path);
			 	 $page->set_context($context);
		 	     echo $page->view();

		 	     return;
             }

		 	 echo "";*/
         }
	 }

     private function serve_media($target, $action){
     	 [$http_message, $context_from_parent] = $this->get_target_response($target, $action, []);
     }
}
