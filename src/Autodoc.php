<?php namespace Anano\Console;

/**
 * Documentation, documentation never changes...
 * The auto-documenter uses reflection to describe and display
 * helpful information about a command class or method.
 */

use ErrorException;
use Reflector;
use ReflectionClass;
use ReflectionMethod;

class Autodoc extends Response
{
    public function __construct(Reflector $ref, $args)
    {
        if ($ref instanceof ReflectionClass) {
            $this->message = $this->docClass($ref, $args);
        }
        else if ($ref instanceof ReflectionMethod) {
            $this->message = $this->docMethod($ref, $args);
        }
        else {
            throw new ErrorException("Unknown reflector type");
        }
    }


    /**
     * Convert a command ("ExampleCommand") to its callable name ("example")
     */
    public static function cmdToName($cmd)
    {
        $cmd = preg_replace('/Command$/', '', $cmd);
        return strtolower($cmd);
    }


    public static function docFiles(array $cmdfiles)
    {
        $cmds = array_map(__CLASS__ . "::cmdToName", array_keys($cmdfiles));
        return new Template('help', ['commandlist' => implode(PHP_EOL, $cmds)]);
    }


    /**
     * Auto-document a command class
     */
    private function docClass($class, $args)
    {
        $lines = [];
        $comment = $this->parseComment( $class->getDocComment() );
        if ($comment)
            $lines[] = $comment;
        
        $lines[] = 'For more information on a particular method, run it with --help option.';
        $lines[] = 'Available methods:';
        $lines[] = '------------------';

        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            // Hide constructors, destructors and deliberately hidden methods (beginning with _) from the doc list.
            if ($method->isConstructor() || $method->isDestructor() || $method->getName()[0] == '_')
                continue;
            
            $lines[] = $args->command . ':' . $method->getName() . ' ' . $this->listParams($method->getParameters());

            $comment = $method->getDocComment();
            if ($comment) {
                $lines[] = str_repeat(' ', 8) . $this->parseComment($comment, true);
            }
        }

        return implode(PHP_EOL, $lines);
    }


    /**
     * Auto-document a command method
     */
    private function docMethod($method, $args)
    {
        $comment = $this->parseComment( $method->getDocComment() );
        $param_list = $this->listParams($method->getParameters());
        $descline = 'Usage: ' . $args->command . ':' . ltrim($args->method, ':_') . ' ' . $param_list . ' [OPTIONS]';

        $lines = [ $descline ];
        
        if ($comment) {
            $lines[] = str_repeat('-', strlen($descline));
            $lines[] = $comment;
        }

        return implode(PHP_EOL, $lines);
    }


    /**
     * List method parameters as string, bracketing optional params.
     * @param  array  getParameters() from any method.
     * @return string
     */
    private function listParams(array $params)
    {
        $list = [];
        foreach ($params as $param) {
            if ($param->isOptional())
                $list[] = "[{$param->getName()}]";
            else
                $list[] = $param->getName();
        }
        return implode(' ', $list);
    }

    /**
     * Remove comment formatting from a doc comment and return plain text.
     * @param  string  $str   A doc comment
     * @param  bool    $only_first_line  Self-explanatory. Use for short descriptions.
     * @return string
     */
    private function parseComment($str, $only_first_line = false)
    {
        $lines = [];
        foreach (preg_split('/[\r\n]+/', $str) as &$line) {
            $line = preg_replace('/^[\s]*[\*\/]+\s{0,1}/', '', $line);

            // Lines in doc comments beginning with # are true comments, hidden in the auto documentation.
            if (isset($line[0]) && $line[0] == '#')
                continue;

            if ($line) {
                if ($only_first_line === true) {
                    return $line;
                }
                $lines[] = $line;
            }
        }
        return implode(PHP_EOL, $lines);
    }
}