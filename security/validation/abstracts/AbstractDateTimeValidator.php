<?php

namespace SaQle\Security\Validation\Abstracts;

abstract class AbstractDateTimeValidator extends AbstractTemporalValidator {
    protected string $format = 'Y-m-d H:i:s';
}
