<?php

header('Content-Type:application/json;charset=UTF-8');

$data = ['ok' => true, 'result' => []];

$uri = ltrim($_SERVER['SCRIPT_NAME'], '/');
$payload = $_GET;
preg_match('#bot(.+)/(.+)#', $uri, $match);

$token = $match[1];
$method = $match[2];

switch ($method) {
    case 'getMe':
        $data['result'] = [
            'id' => 999,
            'first_name' => 'bot name',
            'username' => 'RealNameOfBot',
        ];
        break;

    default:
        $rand_time = rand(5, 10);
        $timeout = (int) $_GET['timeout'];
        if ($rand_time < $timeout) {
            sleep($rand_time);
            $data['result'] = [
                [
                    'update_id' => 1,
                    'message' => [
                        'message_id' => 11,
                        'from' => ['id' => 2],
                        'chat' => ['id' => 3],
                        'date' => time(),
                        'text' => 'message string'
                    ],
                ]
            ];
        } else {
            sleep($timeout);
            $data['result'] = [];
        }

}

echo json_encode($data);
