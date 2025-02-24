<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Support\MessageBag;

/**
 * Class InvalidAttributeException
 *
 * This exception is thrown when invalid attribute data is provided.
 *
 * @package App\Exceptions
 */
class InvalidAttributeException extends Exception
{
    /**
     * An error bag containing errors related to invalid attributes.
     * @var \Illuminate\Contracts\Support\MessageBag $errors
     */
    public $errors;

    public function __construct(MessageBag $errors = null, $message = "Invalid attribute data provided.")
    {
        parent::__construct($message);

        $this->errors = $errors;
    }
}