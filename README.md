# Anano-Console - stand-alone CLI interface / task runner.


## What

This is mainly a simple framework, intended to make it easier to create and manage your own CLI tasks in
any project.

The main feature is automatic documentation using reflection, making it easy to create, use and manage
helper functions, as well as recall how to use them in the future, and share them with other developers.


## Usage

After following the installation instructions below, simply open a terminal in your project root and run:

`php run`

Because of the automatic documentation, most of the use will be self-explanatory.  
You can run any command with `php run filename:methodname` and get help on use by suffixing --help to any command,
with or without method, e.g. `php run command --help` or `php run command:make --help`.

To get started making your own command files, run `php run command:make example`, which will create an
ExampleCommand.php in the first listed directory in the configuration, containing a lot of helpful comments
and example methods. Or if you know what you are doing, pass --clean to get a barebones command file, ready
for you to fill out.

### Parameters

Anano-Console approximates a standard GNU/Bash parameter syntax. There are four types of parameters:

- The command. This must always be the first argument, and will usually consist of a file and method separated by a colon.

- Positional arguments. These have no prefix and are mapped to function parameters in PHP. These must be in a specific order.

- Long options. These are prefixed with `--` and can have a value designated with `=` - e.g. `--arg=value`. Position is irrelevant.

- Short options. Generally aliases of long options, prefixed with `-`, limited to one character and can't have a value. Position is irrelevant.

Short options can be grouped, i.e. `-a -b -c` is the same as `-abc`.  
All options can come before, after or inbetween positional arguments - all that matters there is the order.

### Coding

A command file has access to some helpful methods to access arguments, etc.

- `$this->hasOption('a', 'aaaa')` - (bool) Check if either short argument *a* or long argument *aaaa* is set. Number and order of arguments irrelevant.

- `$this->getOption('aaaa')` (mixed) Get value from long argument *aaaa*, or true if set with no value.

- `$this->readLine()` (mixed) Ask for user input and return it. Can also be used to pause execution.

- `$this->writeLine('string')` (void) Write 'string' to output. Very similar to a simple echo, but enforces *one* line break after.

- `$this->confirm('string')` (bool) Just a shorthand for a write and read, returns true if user agrees.


## Installation

#### For Anano:

Navigate to the root of your [Anano 2](https://github.com/krislux/anano-2) or above installation, then run

```bash
composer require krislux/anano-console
```

Or *require-dev* if you do not want it on your production server.

#### Stand-alone:

Follow the procedure above, but after installation is complete, you must manually copy /vendor/krislux/anano-console/bin/run to your project root and set any necessary permissions.

I haven't been able to figure a way to do this automatically, as Composer doesn't run post-install scripts from libraries.

After this, you may want to open the `run` file in any text editor and have a look at the configuration part near the top.

## License

[MIT license](http://opensource.org/licenses/MIT).
