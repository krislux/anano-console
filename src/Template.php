<?php namespace Anano\Console;

/**
 * Simple class to read a template and replace some symbols with variable values.
 * Template inherits Response, so it has a built-in __toString(), and the
 * contents can be retrieved by simply using the class as a string.
 */

use ErrorException;

class Template extends Response
{
    private $content;

    public function __construct($name, array $symbols = null)
    {
        $filename = realpath(__DIR__ . "/../templates/$name.tpl");
        if ( ! $filename) {
            throw new ErrorException("Template file `$filename` not found.");
        }
        
        $buffer = file_get_contents($filename);

        if ( ! empty($symbols)) {
            foreach ($symbols as $search => $replace) {
                $search = '{'.$search.'}';
                $buffer = str_replace($search, $replace, $buffer);
            }
        }

        $this->message = $buffer;
    }
}