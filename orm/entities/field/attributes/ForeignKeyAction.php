<?php

namespace SaQle\Orm\Entities\Field\Attributes;

enum ForeignKeyAction : string {
     case CASCADE     = "cascade";
     case SET_NULL    = "set_null";
     case RESTRICT    = "restrict";
     case NO_ACTION   = "no_action";
     case SET_DEFAULT = "set_default";
}