<?php

namespace App\Console;

// ~ use Illuminate\Console\Command;
// ~ class ExceptionMakeCommand extends Command

use Illuminate\Console\GeneratorCommand;

class ExceptionMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:exception {name : Exception name, e.g. InvalidUserException}
      {--p|path=App :  E.g. use Api\\\\Users to place it into Api\\Users\\Exceptions .Double backslash or escape with quote}
      {--b|basename=HttpException : Look into vendor/symfony/http-kernel/Exception/ or modify `use` command after the class is generated}
      {--m|message= :  Exception message}
      {--c|code= :  Exception code for JSON response}
      {--*|********** : ------------------------- Common options -----------------------}
      ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new exception';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Exception';

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
      $path = $this->option('path');

      if (substr($path, -1) !== '\\')
      {
        $path .= '\\';
      }
      // ~ $this->laravel->getNamespace();
      return $path;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace_first($this->rootNamespace(), '', $name);

        $path = explode('/', $this->laravel['path']);
        array_pop($path);
        $path[] = str_replace('\\', '/', lcfirst($this->rootNamespace()));

        $path = implode('/', $path);
        return $path.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Exceptions';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/exception.stub';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {

        $stub = parent::buildClass($name);
        $class = $this->option('basename');
        return str_replace('DummyBaseClass', $class, $stub);

    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function fire()
    {
      $message = $this->option('message');

      if (empty($message))
      {
        $message = $this->argument('name');
      }

      $code = $this->option('code');

      if (empty($code))
      {
        // ~ $errors = (include base_path() . '/config/errors.php');
        $errors = config('errors');

        // Find next empty error code


        $codes = [];
        foreach ($errors as $k => $error)
        {
          $codes[] = $error['code'];
        }

        $code = 1000;

        while (true)
        {
          if (!in_array($code, $codes))
          {
            break;
          }

          $code++;

          // Just in case
          if ($code > 9999999)
          {
            $this->error('Could not find a free error code, something is wrong.');
            return;
            break;
          }
        }


      }

      $name = $this->qualifyClass($this->getNameInput());

      if (!isset($errors[$name]))
      {
        $errors[$name] = ['message' => $message, 'code' => (string) $code ];
        uasort($errors, function($a, $b) {
          return $a['code'] <=> $b['code'];
        });

        $output = PHP_EOL;


        foreach ($errors as $class => $error)
        {
          $output .= '    \'' . $class .'\' => [';

          foreach ($error as $k => $v)
          {
            $output .= PHP_EOL .'        \'' . $k .'\' => \'' . $v . '\', ';

          }

          $output .= PHP_EOL . '    ],' . PHP_EOL;
        }

        $fp = fopen(base_path() .'/config/errors.php' , 'w');
        fwrite($fp, '<?php'. PHP_EOL .'return [

    /*
    |—————————————————————————————————————
    | Default Errors
    |—————————————————————————————————————
    */
      ' . $output . '];');
        fclose($fp);
      }


      parent::fire();
      // ~ $message = $this->ask('Enter exception message');
      // ~ $this->info($this->type.' created successfully.');
    }
}