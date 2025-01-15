<?php
namespace SaQle\Auth\Services\Interface;

use SaQle\Services\IService;

interface IAuthService extends IService{
    public function authenticate();
}
?>