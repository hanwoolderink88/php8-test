<?php
declare(strict_types=1);

namespace TestingTimes\Http\Exceptions;

use Exception;

/**
 * should display a 404
 */
class HttpNotFoundException extends Exception
{
    /** The error message */
    protected $message = 'not found';

    /** The error code */
    protected $code = 404;
}
