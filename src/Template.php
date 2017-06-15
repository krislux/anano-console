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

    private static $template_dirs = [];

    public static function setDirs(array $dirs)
    {
        self::$template_dirs = $dirs;
    }

    public function __construct($name, array $symbols = null)
    {
        $filename = $this->findFirstFile($name);
        if ( ! $filename) {
            throw new ErrorException("Template file `$name` not found.");
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

    /**
     * Find the first existing file, prefixing either nothing, one of the
     * paths in `template_dirs` config, or the default template folder.
     */
    private function findFirstFile($name)
    {
        $dirs = self::$template_dirs;
        array_unshift($dirs, '', __DIR__ . '/../templates');

        foreach ($dirs as $dir) {
            $dir .= $dir && $dir[strlen($dir) - 1] !== '/' ? '/' : '';

            foreach (['', '.tpl'] as $ext) {
                $path = $dir . $name . $ext;
                if (file_exists($path))
                    return $path;
            }
        }
        
        return false;
    }
}