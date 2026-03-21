<?php

namespace SaQle\Core\Queue\Jobs;

interface JobInterface {
     public function handle();
     public function middleware();
}