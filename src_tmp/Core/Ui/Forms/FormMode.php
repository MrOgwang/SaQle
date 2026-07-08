<?php
declare(strict_types=1);

namespace SaQle\Core\Ui\Forms;

enum FormMode : string {
     case CREATE = "create";
     case UPDATE = "update";
     case VIEW   = "view";
     case FILTER = "filter";
     case SEARCH = "search";
}