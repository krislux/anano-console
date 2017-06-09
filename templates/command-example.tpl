<?php namespace Anano\Console\Commands;

use ErrorException;
use Anano\Console\Command;
use Anano\Console\Response;

class {name}Command extends Command
{
    /**
     * This function can be called with `php run {lname}:example`
     * Any method arguments will contain any positional arguments passed
     * (i.e. without - or -- prefixed), and can be required or optional as usual.
     * This comment will be displayed as the help text when passing --help,
     * so you should document your commands here.
     * -a  --aaa         Describe option -a / --aaa.
     *     --option      Describe option --option.
     */
    public function example($arg1, $arg2 = null)
    {
        // Check if either short option `-a` or long option `--aaa` is present in the command sent.
        if ($this->hasOption('a', 'aaa')) {}

        // Get the value of long option `--option` or true if present with no value.
        $val = $this->getOption('option');

        // Ask for and return user input. Can also be used to simply pause execution.
        $line = $this->readLine();

        // Write a line to the console. You can also echo it or return a string or Response.
        $this->writeLine("output");

        if ($this->confirm("Do you want to quit?")) {
            return new Response("User exited.");
        }
    }

    /**
     * This optional method is called before any other is run, and allows you to
     * handle any general setup for the class, such as database connections, etc.
     * You can safely remove the method if you don't need it.
     */
    public function __before(\Anano\Console\Arguments $args)
    {
        
    }

    /**
     * This optional method is called after any other is run, and allows any
     * last minute changes to the output. Anything returned from this method
     * is the new final output. You can safely remove this method if not needed.
     */
    public function __after($output)
    {
        
    }

    /**
     * The __index function will be called by running `{lname}`
     * with no method name. This will overwrite the auto documentation,
     * and can be confusing for the user, so generally you will not want this.
     */
    // public function __index()
    // {
    // }
}