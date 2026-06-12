<?php 
namespace SaQle\Auth\interfaces;

interface TenantInterface {
     public function get_id() : mixed;
     public function get_name() : string;
}