<?php

namespace SaQle\Security\Validation\Types;

enum ValueType: string {
     case NUMBER = 'number';
     case TEXT   = 'text';
     case FILE   = 'file';
     case ARRAY  = 'array';
}
