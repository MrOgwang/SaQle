<?php
declare(strict_types=1);

namespace SaQle\Core\Forms;

use SaQle\Core\Support\Session;
use SaQle\Http\Request\RuntimeContext;

final class FormRuntimeContext extends RuntimeContext {
     public function __construct(
         public readonly string  $action = '', //route to handle submission
         public readonly string  $method = '', //http verb (post, put)
         public readonly array   $input  = [], //this is the data coming in from a form submit
         public readonly array   $errors = [], //form validation errors, keyed by field name
         public readonly ?object $model  = null
     ) {}

     public function persist(string $form_name): void {
         Session::set(
             "runtime.form.{$form_name}",
             [
                'action' => $this->action,
                'method' => $this->method,
                'input'  => $this->input,
                'errors' => $this->errors,
                'model'  => $this->model,
             ],
             true
         );
     }

     /**
     * Retrieve a FormRuntimeContext for a given form from the session.
     * Returns a context with empty arrays if not yet submitted.
     */
     public static function from_session(string $form_name): self {
         $data = Session::get("runtime.form.{$form_name}", [
             'action' => '#',
             'method' => 'POST',
             'input'  => [],
             'errors' => [],
             'model'  => null,
         ]);

         return new self(
             action: $data['action'],
             method: $data['method'],
             input: $data['input'],
             errors: $data['errors'],
             model: $data['model']
         );
     }

     /**
     * Factory to create a context from a controller submission
     */
     public static function from_request(string $form_name, array $errors = [], ?object $model = null): self {
         $request = request();
         $context = new self($request->route->url, $request->route->method, $request->data->get_all(), $errors, $model);
         $context->persist($form_name);

         return $context;
     }

     /**
     * Optionally: clear runtime context after successful submission
     */
     public static function clear(string $form_name, string $type = 'form'): void {
         parent::clear($type, $form_name);
     }
}
