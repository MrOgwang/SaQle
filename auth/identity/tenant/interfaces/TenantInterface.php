<?php 
namespace SaQle\Auth\Identity\Tenant\Interfaces;

interface TenantInterface {
     public function get_id() : mixed;
     public function get_name() : string;
}