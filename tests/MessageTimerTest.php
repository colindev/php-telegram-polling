<?php

class MessageTimerTest extends PHPUnit_Framework_TestCase
{
    protected static $test_server_name = '127.0.0.1:9876';
    protected static $test_server_access = '/dev/null';

    public function testRun()
    {
        $tester = $this;
        $conn = new \Rde\Telegram\Connection('x', 'http://'.self::$test_server_name.'/bot');
        $mt = new \Rde\TelegramPolling\MessageTimer($conn, 7);

        $time = 0;
        $launch_time = 0;

        $mt->setPreUpdate(function($payload) use(&$time, &$launch_time, $tester) {
            ++$time;
            \Rde\Terminal::stdout("pre payload {$time} ".json_encode($payload), "\e[31m");
            $tester->assertEquals(7, $payload['timeout']);
            $launch_time = microtime(1);
        });

        $mt->setPostUpdate(function($msg) use(&$time, &$launch_time) {
            $exec_time = microtime(1) - $launch_time;
            \Rde\Terminal::stdout("post {$time} use {$exec_time} ".json_encode($msg), "\e[31m");
        });

        $mt->run(function($msg) use(&$time) {
            \Rde\Terminal::stdout("第 {$time} 次 polling", "\e[33m");
            \Rde\Terminal::stdout(json_encode($msg->value()), "\e[33m");
        }, 3);

        $this->assertEquals(3, $time);
    }

    /** @beforeClass */
    public static function startServer()
    {
        $check_server = function($server_name){
            list($ip, $port) = explode(':', $server_name, 2);
            exec("nc -v -w1 {$ip} {$port} 2>&1", $output);
            return isset($output[0]) && strpos($output[0], 'succeeded!');
        };

        $router = __DIR__.'/server/router.php';
        ! $check_server(self::$test_server_name) and
        exec($cmd = "php -S ".self::$test_server_name." {$router} > ".self::$test_server_access." &");

        isset($cmd) and \Rde\Terminal::stdout($cmd, "\e[33m");

        do {
            \Rde\Terminal::stdout('wait server start', "\e[33m");
            sleep(1);
        } while ( ! $check_server(self::$test_server_name));
        \Rde\Terminal::stdout('server start', "\e[32m");
    }

    /**
     * @afterClass
     */
    public static function shutdownServer()
    {
        exec("pkill -f 'php -S ".self::$test_server_name."'");
        \Rde\Terminal::stdout('server shutdown', "\e[32m");
    }

}
