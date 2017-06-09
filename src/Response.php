<?php namespace Anano\Console;

/**
 * Base class for all sent to output. Contains a string content buffer,
 * and a success code for bash exit codes.
 */

class Response
{
    protected $success = true;
    protected $message;

    /**
     * Create a standard response with a status and content string.
     * @param  bool   $success   Did the process go as intended? Can be omitted.
     * @param  string $message   Content buffer.
     */
    public function __construct($success, $message = null)
    {
        // Allow skipping first param, success is then assumed true.
        if ($message === null && is_string($success)) {
            $this->message = $success;
            $this->success = true;
        }
        else {
            $this->success = $success;
            $this->message = $message;
        }
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getCode()
    {
        return $this->success ? 0 : 1;
    }

    public function __toString()
    {
        return $this->getMessage();
    }
}