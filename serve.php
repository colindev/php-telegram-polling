#!/usr/bin/env php

<?php

require __DIR__.'/vendor/autoload.php';

use Rde\Telegram\Connection;
use Rde\Telegram\Structure\Message;
use Rde\TelegramPolling\MessageTimer;
use Rde\TelegramPolling\CommandManager;
use Rde\Terminal;

$token = isset($argv[1]) ? $argv[1] : null;
if ( ! $token) {
    Terminal::stderr('請輸入token', "\e[31m", 1);
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

// 112320679:AAEVl-Y1ZP_8dBxIX0wLUPDUAozW4JwOTrE
$conn = new Connection($token);
$mt = new MessageTimer($conn, 30);
$cm = new CommandManager(__DIR__.'/commands');

$command_exec_fallback = function(Exception $e){
    Terminal::stderr($e->getMessage(), "\e[31m");
    Terminal::stderr("{$e->getFile()}:{$e->getLine()}", "\e[31m");
};

$mt->run(function($msg) use($conn, $cm, $command_exec_fallback, $verbose) {
    3 <= $verbose and Terminal::stdout(print_r($msg, 1), "\e[33m");
    $command_string = trim(preg_replace("/^@{$conn->me->{'username'}}/", '', $msg->{'message'}->{'text'}));
    3 <= $verbose and Terminal::stdout('command string = '.$command_string);

    $cm->exec(
        $command_string,
        function($output) use($conn, $msg) {
            $reply = new Message($msg->{'message'}->{'chat'}->{'id'});
            $reply->{'text'} = $output;
            $conn->sendMessage($reply);
        },
        $command_exec_fallback
    );

});
