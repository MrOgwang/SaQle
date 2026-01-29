<?php

namespace SaQle\Core\Migration\Interfaces;

interface IMigration{
    public function up();
    public function down();
    public function touched_contexts();
}
