<?php
namespace SaQle\Migration\Base;

abstract class DbSnapshot{
    abstract public function get_models();
    abstract public function get_model_fields();
}
