<?php
namespace SaQle\Core\Queue\Drivers;

interface QueueDriverInterface {
    public function push($queue, $payload, $priority, $delay);
    public function pop($queue);
    public function delete($job_id);
    public function release($job_id, $delay);
    public function fail($job_id, $exception);
}