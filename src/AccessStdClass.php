<?php namespace Rde\TelegramPolling;

use ArrayAccess;
use stdClass;

class AccessStdClass implements ArrayAccess
{
    private $resource;
    public function __construct($o = null)
    {
        $this->resource = $o;
    }

    public function __get($name)
    {
        return $this[$name];
    }

    public function value()
    {
        return $this->resource;
    }

    public function offsetGet($ind)
    {
        $v = null;
        if (is_array($this->resource)) {
            $v = array_key_exists($ind, $this->resource) ? $this->resource[$ind] : null;
        } elseif ($this->resource instanceof stdClass) {
            $v = isset($this->resource->{$ind}) ?  $this->resource->{$ind} : null;
        }

        return new self($v);
    }

    public function offsetSet($ind, $val)
    {
        if ($this->resource instanceof stdClass) {
            $this->resource->{$ind} = $val;
        }

        return $val;
    }

    public function offsetExists($ind)
    {
        return isset($this->resource->{$ind});
    }

    public function offsetUnset($ind)
    {
        if ($this->resource instanceof stdClass) {
            unset($this->resource[$ind]);
        }
    }

    public function __toString()
    {
        return (string) $this->value();
    }
}
