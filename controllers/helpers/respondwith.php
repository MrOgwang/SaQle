<?php
namespace SaQle\Controllers\Helpers;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RespondWith {
     protected string $model;
     protected bool   $from_feeback;

     public function __construct(string $model, bool $from_feeback) {
         $this->model = $model;
         $this->from_feeback = $from_feeback;
     }

     public function get_context() : array {
         return [
             'status'        => 0,
             'first_name'    => '',
             'last_name'     => '',
             'email_address' => '',
             'phone_number'  => '',
             'date_of_birth' => '',
             'message'       => '',
             'gender'        => 'male'
         ];
     }
}
?>
