<?php namespace Rde\TelegramPolling;

class ClosureManager
{
    private $dir;
    public function __construct($dir)
    {
        if ( ! is_readable($dir)) {
            throw new \InvalidArgumentException("{$dir} 無法讀取");
        }
        $this->dir = $dir;
    }

    public function find($name)
    {
        if (is_file($filename = $this->resolveFilename($name))) {
            $closure = include $filename;
            if (is_callable($closure)) return $closure;
        }

        return false;
    }

    protected function resolveFilename($name)
    {
        return "{$this->dir}/{$name}.php";
    }
}
