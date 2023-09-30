<?php

namespace Api\WebSocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;


class sistemaChat implements MessageComponentInterface{
    protected $cliente;

    public function __construct() 
    {
        // Iniciar o objetos que deve armazenar os clientes conectados
        $this->cliente = new \SplObjectStorage;
    }

    //Abrir conexão para novo cliente
    public function onOpen(ConnectionInterface $conn) 
    {
        // Adicionar o cliente na lista
        $this->cliente->attach($conn);

        echo "Nova conexão: {$conn->resourceId} \n\n";
    }
    

    // Eviar mensagens para todos usuários conectados
    public function onMessage(ConnectionInterface $from,  $msg)
    {
        //Percorrer a lisat de usuários conectados
        foreach($this->cliente as $cliente) {

            //Não enviar a mensagem para o usuário que enviou a msg
            if($from !== $cliente){
                //Enviar a msg para usuários
                $cliente->send($msg);
            }
        }
        echo "Usuário {$from->resourceId} enviou uma mensagem \n\n";
    }

    // Desconectar o cliente do websocket
    public function onClose(ConnectionInterface $conn)
    {
        // fechar a conexão e retirar o cliente da lista
        $this->cliente->detach($conn);

        echo "Usuário {$conn->resourceId} desconectou \n\n";
    }
    
    //Será chamado caso aja algum erro no websocket
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        //Fechar conexão
        $conn->close();

        echo "Ocorreu um erro: {$e->getMessage()} \n\n";

    }

}