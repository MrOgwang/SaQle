<?php
namespace SaQle\Auth\Services;

use SaQle\Services\IService;

interface IAuthService extends IService{
    public function authenticate();
}
?>