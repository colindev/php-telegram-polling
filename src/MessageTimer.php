<?php namespace Rde\TelegramPolling;

use Rde\Telegram\Connection;
use Colin\ObjectChain;

class MessageTimer
{
    private $conn;
    private $timeout = 30;
    public $last_update_id = 0;

    private $pre_update;
    private $post_update;

    public function __construct(Connection $conn, $interval)
    {
        $this->conn = $conn;
        $this->timeout = (int) $interval;
    }

    public function setPreUpdate(\Closure $handler)
    {
        $this->pre_update = $handler;
    }

    public function setPostUpdate(\Closure $handler)
    {
        $this->post_update = $handler;
    }

    public function run(\Closure $handler, $time = 0)
    {
        $leave =
            0 === $time ?
            function(){return true;} :
            function() use(&$time) {--$time; return 0 < $time;};

        $payload = array(
            'timeout' => $this->timeout,
            'offset' => 0,
            'limit' => 100,
        );

        $pre_update = $this->pre_update ?: function(){};
        $post_update = $this->post_update ?: function(){};

        do {

            $pre_update($payload);
            $messages = $this->conn->getUpdates($payload);
            $post_update($messages);

            if (empty($messages)) {
                sleep(1);
                continue;
            }

            foreach ($messages as $msg) {
                $handler(new ObjectChain($msg));
            }

            $last = end($messages);
            $this->last_update_id = (int) $last->{'update_id'};
            $payload['offset'] = $this->last_update_id + 1;

        } while ($leave());
    }
}
