<?php namespace Rde\TelegramPolling;

class MessageTimer
{
    private $conn;
    private $timeout = 30;
    public $last_update_id = 0;

    public function __construct(Connection $conn, $interval)
    {
        $this->conn = $conn;
        $this->timeout = (int) $interval;
    }

    public function run(\Closure $handler, $keep_alive = true)
    {
        $payload = array(
            'timeout' => $this->timeout,
            'offset' => 0,
            'limit' => 100,
        );

        do {
            $messages = $this->conn->getUpdates($payload);

            if (empty($messages)) {
                sleep(1);
                continue;
            }

            foreach ($messages as $msg) {
                $handler($msg);
            }

            $last = end($messages);
            $this->last_update_id = (int) $last->{'update_id'};
            $payload['offset'] = $this->last_update_id + 1;

        } while ($keep_alive);
    }
}
