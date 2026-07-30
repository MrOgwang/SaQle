<?php

namespace SaQle\Admin;

class NavigationLink {
     public function __construct(
         public readonly string $label,
         public readonly string $url,
         public readonly ?string $icon = null,
         public readonly bool $show = true,
         public readonly bool $active = false
     ) {}
}