<?php namespace Rde\TelegramPolling;

class Connection
{
    public $me;
    private $url;

    public function __construct($token)
    {
        $this->url = 'https://api.telegram.org/bot'.$token;

        $this->me = $this->getMe();
    }

    protected function resolveData($str)
    {
        $res = json_decode($str);

        if ($res && $res->{'ok'}) return $res->{'result'};

        return false;
    }

    public function __call($method, $params)
    {
        $filename = "{$this->url}/{$method}".(empty($params) ? '' : '?'.http_build_query(array_shift($params)));

        return $this->resolveData(file_get_contents($filename));
    }
}
