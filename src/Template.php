<?php namespace Anano\Console;

use ErrorException;

class Template extends Response
{
    private $content;

    public function __construct($name, array $symbols = null)
    {
        $filename = realpath(__DIR__ . "/../templates/$name.tpl");
        if ( ! file_exists($filename)) {
            throw new ErrorException("Template file `$filename` not found.");
        }
        
        $buffer = file_get_contents($filename);

        if ( ! empty($symbols)) {
            foreach ($symbols as $search => $replace) {
                $search = "\{$search\}";
                str_replace($search, $replace, $buffer);
            }
        }

        $this->message = $buffer;
    }
}