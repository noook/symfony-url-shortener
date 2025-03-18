<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ShortLinkConflictException extends ConflictHttpException
{
    public function __construct(string $shortCode)
    {
        // parent::__construct indicates we are calling the parent's constructor (UnprocessableEntityHttpException).
        // Click on the parent class to see what the parent's constructor does.
        parent::__construct(sprintf('Short link with shortCode "%s" already exists', $shortCode));
    }
}