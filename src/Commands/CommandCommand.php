<?php namespace Anano\Console\Commands;

use ErrorException;
use Anano\Console\Command;
use Anano\Console\Template;

/**
 * Meta-command: The command to make and modify other command files.
 */

class CommandCommand extends Command
{
    /**
     * Create a new Command class file
     * -x  --example    Include helpful example content in the file.
     *     --dir        Specify a directory for the file. If omitted,
     *                  defaults to first dir in `command_dirs`.
     * -c  --confirm    Ask before creating file.
     */
    public function make($name)
    {
        $tpl = $this->hasOption('x', 'example') ? 'command-example' : 'command-clean';
        $buffer = new Template($tpl);

        // Determine directory to place command in. If nothing provided, defaults to first item in 'command_dirs'.
        $command_dirs = $this->getConfig('command_dirs');
        
        $dir = $this->getOption('dir', reset($command_dirs));
        if (ctype_digit($dir)) {
            if (isset($command_dirs[$dir])) {
                $dir = $command_dirs[$dir];
            }
            else {
                throw new ErrorException("Int passed as dir, but no index $dir found in `command_dirs`.");
            }
        }
        else {
            if ( ! is_dir($dir)) {
                throw new ErrorException("`$dir` is not a valid directory.");
            }
        }

        // Build paths
        $dir = realpath($dir);
        $classname = ucfirst($name) . 'Command';
        $filename = $classname . '.php';
        $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if ($this->hasOption('c', 'confirm')) {
            if ( ! $this->confirm("Command file `$classname` will be created at `$path`. Continue?"))
                return false;
        }
        
        // Write file
        if ( ! file_put_contents($path, $buffer)) {
            throw new ErrorException("`$path` is not writable. Check permissions or run as sudo.");
        }
    }
}