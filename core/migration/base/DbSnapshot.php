<?php
namespace SaQle\Core\Migration\Base;

abstract class DbSnapshot{
    abstract public function get_models();
    abstract public function get_model_fields();
    abstract public function get_unique_constraints();
}
