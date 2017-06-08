<?php namespace Anano\Console;

abstract class Command
{
    protected $args;
    
    private $config;

    public final function __construct(Arguments $args, array $config)
    {
        $this->args = $args;
        $this->config = $config;
    }

    /**
     * Wait for input from terminal. Can be used for user input or just to pause execution.
     */
    protected function readLine()
    {
        return trim(fgets(STDIN));
    }

    /**
     * Shorthand for asking for confirmation.
     */
    protected function confirm($str)
    {
        echo $str . ' [y/N]';
        return $this->readLine() === 'y';
    }

    /**
     * Get value from configuration set in the bin.
     */
    protected function getConfig($key, $def = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return $def;
    }

    /**
     * Shorthand functions for Arguments
     */
    protected function hasOption()
    {
        return $this->args->has(func_get_args());
    }
    protected function getOption($key, $def = null)
    {
        return $this->args->get($key, $def);
    }
}