<?php

use Api\WebSocket\sistemaChat;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

require __DIR__ . '\vendor\autoload.php';

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new sistemaChat()
        )
    ),
    8080
);

// iniciar servidor
$server->run();

