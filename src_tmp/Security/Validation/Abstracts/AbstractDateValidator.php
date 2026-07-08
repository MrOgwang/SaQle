<?php

namespace SaQle\Security\Validation\Abstracts;

abstract class AbstractDateValidator extends AbstractTemporalValidator {
    protected string $format = 'Y-m-d';
}
