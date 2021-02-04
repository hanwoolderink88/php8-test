<?php
declare(strict_types=1);

namespace TestingTimes\Http\Exceptions;

use Exception;

/**
 * should display a 404
 */
class HttpNotFoundException extends Exception
{
    protected $message = 'not found';

    protected $code = 404;
}
