<?php namespace Rde\TelegramPolling;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandManager
{
    private $app;
    private $commands_dir;

    public function __construct($commands_dir)
    {
        $this->app = new Application();
        $this->commands_dir = $commands_dir;
        if ( ! is_readable($this->commands_dir)) {
            throw new \InvalidArgumentException("{$commands_dir} 無法讀取");
        }
    }

    public function exec($command_string, \Closure $callback, \Closure $fallback = null)
    {
        $dir = opendir($this->commands_dir);
        while ($filename = readdir($dir)) {
            if (preg_match('/^(\w+)\.php$/', $filename, $m)) {
                $cmd = $m[1];
                $this->app->add(new $cmd);
            }
        }
        closedir($dir);

        $output = new BufferedOutput();

        $result = 2;

        if (preg_match('/^\/(\w+(?::\w+)?)(?:\s+.*)?$/', $command_string, $match)) {

            $command_name = $match[1];

            if ( ! $this->app->has($command_name)) {
                $fallback and $output->write($fallback(new \Exception("command [{$command_name}] not find")));
                return null;
            }

            $cmd = $this->app->find($command_name);

            try {
                $result = $cmd->run(new StringInput($command_string), $output);
                $callback($output->fetch());
            } catch (\Exception $e) {
                $result = 1;
                $fallback($e);
            }
        }

        return $result;
    }

    public function __call($method, $params)
    {
        return call_user_func_array(array($this->app, $method), $params);
    }
}
