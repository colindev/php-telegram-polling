#!/usr/bin/env php

<?php

require __DIR__.'/vendor/autoload.php';

use Rde\Telegram\Connection;
use Rde\Telegram\Structure;
use Rde\TelegramPolling\MessageTimer;
use Rde\TelegramPolling\CommandManager;
use Rde\TelegramPolling\ClosureManager;
use Rde\Terminal;

$token = isset($argv[1]) ? $argv[1] : null;
if ( ! $token) {
    Terminal::stderr("Usage: {$argv[0]} <token> [-vvv]", "\e[31m", 1);
    die;
}

if (in_array('-vvv', $argv)) {
    $verbose = 3;
} elseif (in_array('-vv', $argv)) {
    $verbose = 2;
} elseif (in_array('-v', $argv)) {
    $verbose = 1;
} else {
    $verbose = 0;
}

$conn = new Connection($token);
$mt = new MessageTimer($conn, 30);
$cm = new CommandManager(__DIR__.'/commands');
$csm = new ClosureManager(__DIR__.'/closure');

$command_exec_fallback = function(Exception $e) use($verbose) {
    Terminal::stderr($e->getMessage(), "\e[31m");
    Terminal::stderr("{$e->getFile()}:{$e->getLine()}", "\e[31m");
    3 <= $verbose and Terminal::stderr($e->getTraceAsString(), "\e[31m");
};

Terminal::stdout('bot: '.json_encode($conn->me), "\e[32m");

3 <= $verbose and $mt->setPostUpdate(function($raw){
    Terminal::stdout('post update: '.json_encode($raw), "\e[35m");
});

$mt->run(function($msg) use($conn, $cm, $csm, $command_exec_fallback, $verbose) {
    3 <= $verbose and Terminal::stdout(print_r($msg->value(), 1), "\e[33m");
    $command_string = trim(preg_replace("/^@{$conn->me->{'username'}}/", '', $msg->{'message'}->{'text'}));
    3 <= $verbose and Terminal::stdout('command string = '.$command_string);

    $reply = new Structure();
    $reply->{'chat_id'} = $msg->{'message'}->{'chat'}->{'id'}->value();

    // closure
    if ($cmd = $csm->find($command_string)) {
        $reply->{'text'} = $cmd($msg, $conn);
        $conn->sendMessage($reply);
        return;
    }

    // command
    $cm->exec(
        $command_string,
        array('message' => $msg, 'connection' => $conn),
        function($output) use($conn, $reply) {
            $reply->{'text'} = $output;
            $conn->sendMessage($reply);
        },
        $command_exec_fallback
    );

});
