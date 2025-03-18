<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ShortLinkTooLongException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        // parent::__construct indicates we are calling the parent's constructor (UnprocessableEntityHttpException).
        // Click on the parent class to see what the parent's constructor does.
        parent::__construct('Short link is too long');
    }
}