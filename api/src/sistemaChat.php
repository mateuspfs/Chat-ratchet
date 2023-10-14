<?php

namespace Api\WebSocket;

use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class sistemaChat implements MessageComponentInterface {
    protected $cliente;

    protected $userConnections = [];

    protected $userConversations = [];

    public function __construct() 
    {
        // Iniciar o objeto que deve armazenar os clientes conectados
        $this->cliente = new \SplObjectStorage;
    }

    // Abrir conexão para novo cliente
    public function onOpen(ConnectionInterface $conn) 
    {
        // Obtenha as conversas às quais o usuário pertence a partir do banco de dados
        $conversations = $this->conversasDoUsuario($conn);

        // Associe o usuário às conversas
        foreach ($conversations as $conversation) {
            $this->userConversations[$conn->resourceId][] = $conversation['id_conversa'];
        }

        // Adicionar o cliente na lista
        $this->cliente->attach($conn);

        echo "Nova conexão: {$conn->resourceId }\n\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Verificar se o usuário está autorizado a enviar mensagens para a conversa
        $userConversations = $this->userConversations[$from->resourceId] ?? [];

        // Itere sobre as conversas do usuário e envie a mensagem para os participantes de cada conversa
        foreach ($userConversations as $conversationId) {
            // Envie a mensagem para os participantes desta conversa
            $this->mandarMensagemConversa($conversationId, $msg, $from);
        }
        $this->salvarMensagemNoBanco($msg);
    }

    public function onClose(ConnectionInterface $conn)
    {
        // fechar a conexão e retirar o cliente da lista
        $this->cliente->detach($conn);

        echo "Usuário {$conn->resourceId} desconectou \n\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        // Fechar conexão
        $conn->close();

        echo "Ocorreu um erro: {$e->getMessage()} \n\n";
    }

    public function conversasDoUsuario(ConnectionInterface $conn) {
        // Pega os IDs das conversas às quais o usuário está ligado 
        $dbConnection = new dbConnection();
        $conn = $dbConnection->getConnect();

        $queryConvUser = $conn->prepare('SELECT c.id_conversa 
                                            FROM conversas as c
                                                INNER JOIN participante_conversa as pc 
                                                    ON c.id_conversa = pc.id_conversa
                                                INNER JOIN usuarios as u 
                                                    ON u.id_user = pc.id_user
                                                        WHERE u.id_user = :id_user');

        $queryConvUser->bindParam(':id_user', $_SESSION['id_user']);
        $queryConvUser->execute();
        
        $resultConvs = $queryConvUser->fetchAll();

        return $resultConvs;
    }

    private function mandarMensagemConversa($conversationId, $message, $from) {
        // Certifique-se de que o usuário atual esteja autorizado a enviar mensagens para esta conversa
        if ($this->verificaçãoUsuarioConversa($conversationId, $from)) {
            // Obtenha uma lista de todos os participantes da conversa
            $participants = $this->participantesConversa($conversationId);
    
            // Enviar a mensagem para todos os participantes
            foreach ($participants as $participant) {   
                $participantConnection = $this->userConnection($participant['id_user']);
                
                // Verifique se o participante está online (conectado via WebSocket)
                if ($participantConnection !== null) {
                    $participantConnection->send($message);
                }
            }
        }
    }

    private function verificaçãoUsuarioConversa($conversationId, $from) {
        // Verifique se o usuário atual está autorizado a enviar mensagens para esta conversa
        $userId = $this->idUserConnection($from);
    
        // Consultando o banco de dados para verificar se o usuário está associado à conversa.
        $dbConnection = new dbConnection();
        $conn = $dbConnection->getConnect();
    
        $query = "SELECT COUNT(*) AS authorized FROM participante_conversa WHERE id_conversa = :conversationId AND id_user = :userId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result['authorized'] > 0;
    }

    private function participantesConversa($conversationId) {
        // Obter a lista de todos os participantes da conversa com base no ID da conversa.
        // Consulte a tabela 'participante_conversa' para obter a lista de participantes.

        $dbConnection = new dbConnection();
        $conn = $dbConnection->getConnect();

        $query = "SELECT id_user FROM participante_conversa WHERE id_conversa = :conversationId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $participants;
    }

    private function userConnection($userId) {
        // Obtenha a conexão WebSocket do usuário com base no seu ID.
        // Você pode percorrer a lista de conexões $this->cliente para encontrar a conexão correta.

        foreach ($this->cliente as $conn) {
            // Verifique se a conexão está associada ao usuário com o ID correspondente.
            if ($this->idUserConnection($conn) == $userId) {
                return $conn;
            }
        }
        return null; // Retorna null se o usuário não estiver online.
    }

    private function idUserConnection(ConnectionInterface $conn) {
        // Obtenha o ID do usuário com base na conexão WebSocket usando a variável de sessão.
        if (isset($_SESSION['id_user'])) {
            $userId = $_SESSION['id_user'];

            // Verifique se a conexão está associada a um ID de usuário válido.
            if ($userId !== null && isset($this->userConnections[$userId])) {
                return $userId;
            }
        }

        return null; // Retorna null se não encontrar uma associação válida.
    }

    private function salvarMensagemNoBanco($mensagem)
    {
        $dbConnection = new dbConnection();
        $conn = $dbConnection->getConnect();

        $queryMsg = "INSERT INTO mensagens(mensagem_text, id_user, id_conversa, data_registro) 
                     VALUES (:mensagem, :id_user, :id_conversa, :data_registro)";
        
        $mensagemArray = json_decode($mensagem, true);

        $addMensagem = $conn->prepare($queryMsg);

        $timezone = new DateTimeZone('America/Sao_Paulo');
        $currentDateTime = new DateTime('now', $timezone);   

        $addMensagem->bindParam(':mensagem', $mensagemArray['mensagem']);
        $addMensagem->bindParam(':id_user', $mensagemArray['id_user']);
        $addMensagem->bindParam(':id_conversa', $mensagemArray['id_conversa']);
        $addMensagem->bindParam(':data_registro', $currentDateTime);

        $addMensagem->execute();
    }
}
