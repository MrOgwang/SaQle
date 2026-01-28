<?php

namespace SaQle\Security\Validation\Types;

enum ValidationMode {
     case FAIL_FAST;
     case COLLECT_ALL;
}
