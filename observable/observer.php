<?php
namespace SaQle\Observable;

interface Observer{
     public function update(Observable $observable);
}
?>