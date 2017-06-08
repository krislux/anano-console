<?php namespace Anano\Console;

class Response
{
    protected $success = true;
    protected $message;

    /**
     * 
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