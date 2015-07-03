<?php namespace Rde\TelegramPolling;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandManager
{
    private $app;

    public function __construct(array $config)
    {
        $this->app = new Application();
    }

    public function exec($command_name, $payload, \Closure $fallback = null)
    {
        $output = new BufferedOutput();

        if ( ! $this->app->has($command_name)) {
            $fallback and $output->write($fallback($command_name, $payload));
            return null;
        }

        $input = new StringInput($payload);
        $cmd = $this->app->find($command_name);

        return $cmd->run($input, $output);
    }

    public function __call($method, $params)
    {
        return call_user_func_array(array($this->app, $method), $params);
    }
}
