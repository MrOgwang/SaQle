<?php

namespace SaQle\Console;

use SaQle\Console\Signature\Signature;

abstract class Command {
     abstract public function signature(): Signature;

     abstract public function handle(CommandContext $context): int;
}