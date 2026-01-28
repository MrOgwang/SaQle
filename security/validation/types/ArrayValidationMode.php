<?php

namespace SaQle\Security\Validation\Types;

enum ArrayValidationMode {
    case ALL_ITEMS_MUST_PASS;
    case AT_LEAST_ONE_MUST_PASS;
}
