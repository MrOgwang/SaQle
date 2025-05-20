<?php
declare(strict_types = 1);
namespace SaQle\Orm\Entities\Field\Formatter;
enum FormatterOptions{
    case UPPERCASE;
    case LOWERCASE;
    case CAPITALIZE;
    case ENCRYPT;
}
