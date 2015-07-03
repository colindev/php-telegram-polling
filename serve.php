#!/usr/bin/env php

<?php

require __DIR__.'/vendor/autoload.php';

use Rde\TelegramPolling\Connection;
use Rde\TelegramPolling\MessageTimer;

$mt = new MessageTimer(new Connection('112320679:AAEVl-Y1ZP_8dBxIX0wLUPDUAozW4JwOTrE'), 30);

$mt->run(function($msg){
    echo json_encode($msg), PHP_EOL;
});
