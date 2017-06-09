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

    /**
     * Build list of command files for later autoloading.
     * @param  Arguments  $args    @see \Anano\Console\Arguments
     * @param  array      $config  Array of configuration items.
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
     * Autoload and call the specified command class and method,
     * or run the auto-documenter if --help provided or invalid command.
     * @return Response
     */
    public function dispatch()
    {
        if ( ! $this->args->command) {
            $cmds = array_map(__NAMESPACE__ . "\\Autodoc::cmdToName", array_keys($this->files));
            return new Template('help', ['commandlist' => implode(PHP_EOL, $cmds)]);
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

            // Method was given.
            if ($this->args->method && $class->hasMethod($this->args->method)) {

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
                return new Response($rv !== false, $rv !== false ? "Done." : "Exited.");
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