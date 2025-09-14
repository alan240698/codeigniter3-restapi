<?php

defined('BASEPATH') or exit('No direct script access allowed');

class ApiResponseService
{
    protected $CI;
    protected $defaultHeaders = [
        'Content-Type'                  => 'application/json; charset=utf-8',
        'Access-Control-Allow-Origin'   => '*',
        'Access-Control-Allow-Methods'  => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'  => 'Content-Type, Authorization, X-Requested-With'
    ];

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->setDefaultHeaders();
    }

    /**
     * Send success response
     * 
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param array $meta
     * @return void
     */
    public function success($data = null, $message = 'Success', $statusCode = 200, array $meta = [])
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        $this->sendResponse($response, $statusCode);
    }

    /**
     * Send error response
     * 
     * @param string $message
     * @param int $statusCode
     * @param string $errorCode
     * @param array $errors
     * @return void
     */
    public function error($message = 'An error occurred', $statusCode = 500, $errorCode = null, array $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errorCode) {
            $response['error_code'] = $errorCode;
        }

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->sendResponse($response, $statusCode);
    }

    /**
     * Send validation error response
     * 
     * @param array $errors
     * @param string $message
     * @return void
     */
    public function validationError(array $errors, $message = 'Validation failed')
    {
        $this->error($message, 422, 'VALIDATION_ERROR', $errors);
    }

    /**
     * Send not found response
     * 
     * @param string $message
     * @return void
     */
    public function notFound($message = 'Resource not found')
    {
        $this->error($message, 404, 'NOT_FOUND');
    }

    /**
     * Send unauthorized response
     * 
     * @param string $message
     * @return void
     */
    public function unauthorized($message = 'Unauthorized')
    {
        $this->error($message, 401, 'UNAUTHORIZED');
    }

    /**
     * Send forbidden response
     * 
     * @param string $message
     * @return void
     */
    public function forbidden($message = 'Forbidden')
    {
        $this->error($message, 403, 'FORBIDDEN');
    }

    /**
     * Send method not allowed response
     * 
     * @param string $message
     * @return void
     */
    public function methodNotAllowed($message = 'Method not allowed')
    {
        $this->error($message, 405, 'METHOD_NOT_ALLOWED');
    }

    /**
     * Send conflict response
     * 
     * @param string $message
     * @return void
     */
    public function conflict($message = 'Resource conflict')
    {
        $this->error($message, 409, 'CONFLICT');
    }

    /**
     * Send created response
     * 
     * @param mixed $data
     * @param string $message
     * @return void
     */
    public function created($data = null, $message = 'Resource created successfully')
    {
        $this->success($data, $message, 201);
    }

    /**
     * Send no content response
     * 
     * @return void
     */
    public function noContent()
    {
        $this->CI->output->set_status_header(204);
        $this->CI->output->_display();
        exit();
    }

    /**
     * Send paginated response
     * 
     * @param array $data
     * @param array $pagination
     * @param string $message
     * @return void
     */
    public function paginated(array $data, array $pagination, $message = 'Success')
    {
        $this->success($data, $message, 200, ['pagination' => $pagination]);
    }

    /**
     * Handle exception and send appropriate response
     * 
     * @param Exception $exception
     * @return void
     */
    public function handleException(Exception $exception)
    {
        // Log the exception
        log_message('error', 'API Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

        // Handle specific exception types
        if ($exception instanceof PostNotFoundException) {
            $this->notFound($exception->getMessage());
        } elseif ($exception instanceof ValidationException) {
            $this->validationError($exception->getErrors(), $exception->getMessage());
        } elseif ($exception instanceof PostAlreadyExistsException) {
            $this->conflict($exception->getMessage());
        } elseif ($exception instanceof InvalidArgumentException) {
            $this->error($exception->getMessage(), 400, 'BAD_REQUEST');
        } else {
            // Generic server error
            $message = ENVIRONMENT === 'production'
                ? 'Internal server error'
                : $exception->getMessage();

            $this->error($message, 500, 'INTERNAL_ERROR');
        }
    }

    /**
     * Send response with proper headers and status
     * 
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    protected function sendResponse(array $data, $statusCode = 200)
    {
        // Add timestamp
        $data['timestamp'] = date('c');

        // Add request ID for tracking (if available)
        if (isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            $data['request_id'] = $_SERVER['HTTP_X_REQUEST_ID'];
        }

        $this->CI->output
            ->set_status_header($statusCode)
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Set default headers
     * 
     * @return void
     */
    protected function setDefaultHeaders()
    {
        foreach ($this->defaultHeaders as $name => $value) {
            $this->CI->output->set_header($name . ': ' . $value);
        }
    }

    /**
     * Add custom header
     * 
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->CI->output->set_header($name . ': ' . $value);
    }

    /**
     * Handle CORS preflight requests
     * 
     * @return void
     */
    public function handleCors()
    {
        if ($this->CI->input->method() === 'options') {
            $this->CI->output
                ->set_status_header(200)
                ->set_header('Access-Control-Max-Age: 86400')
                ->_display();
            exit();
        }
    }
}
