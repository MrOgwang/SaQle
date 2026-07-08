<?php

namespace SaQle\Security\Validation\Abstracts;

abstract class AbstractTimeValidator extends AbstractTemporalValidator {
    protected string $format = 'H:i:s';
}
