<?php
namespace SaQle\Core\Queue\Middleware;

interface JobMiddlewareInterface {
     public function handle($job, $next);
}