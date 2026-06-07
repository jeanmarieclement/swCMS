<?php

namespace App\Exceptions;

/**
 * Security Exception
 * Thrown when security validation fails (CSRF, authentication, authorization, etc.)
 *
 * @package App\Exceptions
 * @author swCMS Team
 */
class SecurityException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code HTTP status code (default 403 Forbidden)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Security validation failed',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
