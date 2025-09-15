<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Post Exception
 */
abstract class PostException extends Exception
{
    protected $errorCode;
    protected $httpStatusCode;

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errorCode        = $this->getErrorCode();
        $this->httpStatusCode   = $this->getHttpStatusCode();
    }

    abstract protected function getErrorCode();
    abstract protected function getHttpStatusCode();

    public function getHttpStatus()
    {
        return $this->httpStatusCode;
    }

    public function getApiErrorCode()
    {
        return $this->errorCode;
    }

    public function toArray()
    {
        return [
            'error'         => true,
            'error_code'    => $this->getApiErrorCode(),
            'message'       => $this->getMessage(),
            'http_status'   => $this->getHttpStatus()
        ];
    }
}

/**
 * Post Not Found Exception
 */
class PostNotFoundException extends PostException
{
    protected function getErrorCode()
    {
        return 'POST_NOT_FOUND';
    }

    protected function getHttpStatusCode()
    {
        return 404;
    }
}

/**
 * Post Already Exists Exception
 */
class PostAlreadyExistsException extends PostException
{
    protected function getErrorCode()
    {
        return 'POST_ALREADY_EXISTS';
    }
    
    protected function getHttpStatusCode()
    {
        return 409;
    }
}

/**
 * Validation Exception
 */
class ValidationException extends PostException {
    protected $errors;

    public function __construct($errors, $message = "Validation failed", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errors = is_array($errors) ? $errors : [$errors];
    }

    protected function getErrorCode()
    {
        return 'VALIDATION_ERROR';
    }

    protected function getHttpStatusCode()
    {
        return 422;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->getErrors()
        ]);
    }
}

/**
 * Invalid Input Exception
 */
class InvalidInputException extends PostException
{
    protected function getErrorCode()
    {
        return 'INVALID_INPUT';
    }

    protected function getHttpStatusCode()
    {
        return 400;
    }
}