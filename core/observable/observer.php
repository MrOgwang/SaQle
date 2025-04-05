<?php
namespace SaQle\Core\Observable;

interface Observer{
     public function update(Observable $observable);
}
?>