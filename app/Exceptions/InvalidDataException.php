<?php

namespace App\Exceptions;

use Exception;

class InvalidDataException extends Exception
{
    /**
     * Custom exception for handling data validation errors
     * 
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(
        string $message = "Invalid data provided", 
        int $code = 400, 
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response
     */
    public function render()
    {
        return response()->json([
            'error' => 'Data Validation Error',
            'message' => $this->getMessage()
        ], $this->code);
    }
}
