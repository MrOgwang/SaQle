<?php

namespace SaQle\Migration\Interfaces;

interface IMigration{
    public function up();
    public function down();
    public function touched_contexts();
}
