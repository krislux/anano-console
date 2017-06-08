<?php namespace Anano\Console;

use ArrayAccess;

class Arguments implements ArrayAccess
{
    public $count = 0;
    public $bin;
    public $command;
    public $method;
    public $positional = [];
    public $options = [];
    

    public function __construct(array $args)
    {
        $this->count = count($args);
        
        $this->bin = $args[0];

        if ($this->count >= 1) {
            $cparts = explode(':', $args[1], 2);
            $this->command = $cparts[0];
            if (isset($cparts[1])) {
                $this->method = $cparts[1];
            }
        }

        if ($this->count >= 2) {
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
     * @param  string  Option to get value for.
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
     * 
     */
    private function parseOptions($options)
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