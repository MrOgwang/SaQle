<?php
namespace SaQle\Core\Chain\Interfaces;

interface Handler{

    public function set_next(Handler $handler): Handler;
    public function handle(mixed $request) : mixed;

}


