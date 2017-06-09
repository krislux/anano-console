<?php namespace Anano\Console;

/**
 * Parse and keep command line arguments in an easy to read container.
 * Arguments inherits ArrayAccess, so it can be used as an array, but with no
 * risk of crashing on non-existent value.
 * E.g. $arguments['test'] is the same $arguments->get('test');
 */

use ArrayAccess;

class Arguments implements ArrayAccess
{
    public $count = 0;       // Number of arguments, including caller
    public $bin;             // The calling file
    public $command;         // Command class called, if present
    public $method;          // Command method called, if present
    public $positional = []; // Any positional arguments
    public $options = [];    // Any short and long options passed
    
    /**
     * @param  array  $args  The $argv from a cli call.
     */

    public function __construct(array $args)
    {
        $this->count = count($args);
        
        $this->bin = $args[0];
        $this->method = '__index';

        if ($this->count >= 2) {
            $cparts = explode(':', $args[1], 2);
            $this->command = $cparts[0];
            if (isset($cparts[1])) {
                $this->method = $cparts[1];
            }
        }

        if ($this->count >= 3) {
            $this->parseOptions( array_slice($args, 2) );
        }
    }
    
    /**
     * Returns whether one or more options exist. Does not return value. Any match will result in true.
     * @param  splat|array ... Any number of key strings to check.
     * @return bool
     */
    public function has()
    {
        $args = func_get_args();
        // Allow both array and splat as argument.
        if (is_array($args[0])) {
            $args = $args[0];
        }

        foreach ($args as $key) {
            $key = ltrim($key, '-');    // Remove leading dashes in case people use them in command classes.
            if (isset($this->options[$key])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the value of a single option. Short options and valueless long options will return true.
     * @param  string  $key  Option to get value for.
     * @param  mixed   $def  Default value to return if key doesn't exist.
     * @return string
     */
    public function get($key, $def = null)
    {
        $key = trim($key, '-');
        if (isset($this->options[$key])) {
            if ($this->options[$key]) {
                return $this->options[$key];
            }
            return true;
        }
        return $def;
    }

    /**
     * Read and parse command line options, placing them in the public vars.
     * @param  array  $options
     * @return void
     */
    private function parseOptions(array $options)
    {
        foreach ($options as $option) {
            
            if ($option[0] == '-') {
                
                // Long option
                if (isset($option[1]) && $option[1] == '-') {

                    $option = substr($option, 2);   // Remove the --
                    
                    $key = $option;
                    $val = true;
                    if (strpos($option, '=')) {
                        list($key, $val) = explode('=', $option, 2);
                    }
                    $this->options[$key] = $val;

                }
                // Short option
                else {
                    
                    $len = strlen($option);
                    for ($i = 1; $i < $len; $i++) {
                        $this->options[$option[$i]] = true;
                    }
                }
            }
            else {
                // Positional argument
                $this->positional[] = $option;
            }
        }
    }


    /**
     * ArrayAccess
     */
    
     public function offsetExists($offset)
     {
        return $this->has($offset);
     }

     public function offsetGet($offset)
     {
        return $this->get($offset);
     }

     public function offsetSet($offset, $value)
     {
        throw new ErrorException('You cannot modify CLI arguments.');
     }

     public function offsetUnset($offset)
     {
        throw new ErrorException('You cannot modify CLI arguments.');
     }
}