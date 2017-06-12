<?php namespace Anano\Console;

/**
 * The router handles autoloading, method routing and waking up the autodoc.
 */

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
    private $time_start;

    /**
     * Build list of command files for later autoloading.
     * @param  Arguments  $args    @see \Anano\Console\Arguments
     * @param  array      $config  Array of configuration items.
     */
    public function __construct(Arguments $args, array $config)
    {
        $this->args = $args;
        $this->config = $config;
        $this->time_start = microtime(true);

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
     * Display some quick profiling information on exit if --profile is passed.
     * Works with any command and method. --profile can still be used as a custom
     * option in any command, although this message will be added.
     */
    public function __destruct()
    {
        if ($this->args->has('profile'))
        {
            $td = microtime(true) - $this->time_start;
            $td = $td < 1 ? round($td * 1000, 2) . "ms" : round($td, 2) . "s";
            $mem = round(memory_get_peak_usage(false) / 1048576, 2) . " MB";
            printf("\nRuntime: %s - mem: %s", $td, $mem);
        }
    }

    /**
     * Autoload and call the specified command class and method,
     * or run the auto-documenter if --help provided or invalid command.
     * @return Response
     */
    public function dispatch()
    {
        if ( ! $this->args->command) {
            return Autodoc::docFiles($this->files);
        }

        $cmdname = ucfirst($this->args->command) . 'Command';
        if (isset($this->files[$cmdname])) {
            require $this->files[$cmdname];

            // Init the command class
            $cmdname = "\\Anano\\Console\\Commands\\$cmdname";
            $command = new $cmdname($this->args, $this->config);
            if ( ! $command instanceof Command) {
                throw new ErrorException("$cmdname did not extend Command");
            }

            $class = new ReflectionClass($command);

            // Check if method exists
            if ( ! $class->hasMethod($this->args->method)) {
                // Try with _ prefix, which can be used to name methods as reserved words.
                if ($class->hasMethod('_' . $this->args->method)) {
                    $this->args->method = '_' . $this->args->method;
                }
                else {
                    $this->args->method = null; // No such method.
                }
            }

            // Method was given.
            if ($this->args->method && $this->args->method) {

                // Magic preprocessor method
                if ($class->hasMethod('__before')) {
                    $command->__before($this->args);
                }
                
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
                    // Run method
                    $rv = $method->invokeArgs($command, $this->args->positional);
                } catch (Exception $exc) {
                    return new Response(false, $exc->getMessage());
                }

                // Magic postprocessor method
                if ($class->hasMethod('__after')) {
                    $rv = $command->__after($rv);
                }

                // Figure out what to return and print at the end.
                if ($rv instanceof Response) {
                    return $rv;
                }
                else if (is_string($rv)) {
                    return new Response(true, $rv);
                }
                return new Response($rv !== false);
            }
            // No method was given.
            else {
                return new Autodoc($class, $this->args);
            }
        }
        else {
            return new Response(false, "Command `{$this->args->command}` not found.");
        }
    }
}