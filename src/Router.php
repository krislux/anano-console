<?php namespace Anano\Console;

use Exception;
use ErrorException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;

final class Router
{
    private $args;
    private $config;
    private $files = [];

    /**
     * 
     */
    public function __construct(Arguments $args, array $config)
    {
        $this->args = $args;
        $this->config = $config;

        $dirs = $config['command_dirs'];
        $dirs[] = __DIR__ . '/Commands';

        // Find all potential command files in the folders defined in the bin, plus the built-in dir.
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                foreach ($iterator as $file) {
                    $name = $file->getBasename('.php');
                    $this->files[$name] = $file->getPathname();
                }
            }
        }
    }

    /**
     * 
     */
    public function dispatch()
    {
        if ( ! $this->args->command) {
            return new Template('help');
        }

        $cmdname = ucfirst($this->args->command) . 'Command';
        if (isset($this->files[$cmdname])) {
            require $this->files[$cmdname];

            // Init the command class
            $cmdname = "\\Anano\\Console\\Commands\\$cmdname";
            $command = new $cmdname($this->args, $this->config);

            $class = new ReflectionClass($command);

            if ($this->args->method && $class->hasMethod($this->args->method)) {
                
                $method = new ReflectionMethod($command, $this->args->method);

                // If the option "--help" is provided, automatically display help.
                if ($this->args->has('help')) {
                    return new Autodoc($method, $this->args);
                }
                
                $reqnum = $method->getNumberOfRequiredParameters();
                if ($reqnum > count($this->args->positional)) {
                    return new Response(false, "This method requires at least $reqnum positional argument(s). Use --help for help.");
                }
                
                try {
                    $rv = $method->invokeArgs($command, $this->args->positional);
                } catch (Exception $exc) {
                    return new Response(false, $exc->getMessage());
                }

                return new Response($rv !== false, $rv !== false ? "Done." : "Exited.");
            }
            else {
               return new Autodoc($class, $this->args);
            }
        }
        else {
            return new Response(false, "Command `{$this->args->command}` not found.");
        }
    }
}