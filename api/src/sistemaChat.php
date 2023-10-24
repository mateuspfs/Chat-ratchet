<?php

namespace Api\WebSocket;

use Exception;
use PDO;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class sistemaChat implements MessageComponentInterface {
    protected $cliente;

    protected $ConexaoUsuarios = [];

    protected $userConversations = [];

    public function __construct() 
    {
        // Iniciar o objeto que deve armazenar os clientes conectados
        $this->cliente = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        try {
            $id_user = $this->pegarIdUsusarioConectado($conn);
    
            if ($id_user !== null) {
                // Associe a conexão WebSocket ao id_user
                $this->ConexaoUsuarios[$id_user] = $conn;
    
                // Obtenha as conversas às quais o usuário pertence a partir do banco de dados
                $conversations = $this->conversasDoUsuario($id_user);
    
                // Associe o usuário às conversas
                foreach ($conversations as $conversationId) {
                    if (!isset($this->userConversations[$conn->resourceId])) {
                        $this->userConversations[$conn->resourceId] = [];
                    }
                    $this->userConversations[$conn->resourceId][] = $conversationId;
                }
    
                // Adicione o cliente na lista
                $this->cliente->attach($conn);
    
                echo "Nova conexão para o usuário {$id_user} ({$conn->resourceId})\n\n";
            } else {
                // Feche a conexão se o id_user não for fornecido
                $conn->close();
            }
        } catch (Exception $e) {
            // Lidar com erros ao abrir a conexão
            echo 'Erro ao abrir a conexão: ' . $e->getMessage();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Verificar se o usuário está autorizado a enviar mensagens para a conversa
        $userConversations = $this->userConversations[$from->resourceId] ?? [];
    
        // Itere sobre as conversas do usuário e envie a mensagem para os participantes de cada conversa
        foreach ($userConversations as $conversationId) {
            try {
                // Envie a mensagem para os participantes desta conversa
                $this->mandarMensagemConversa($conversationId, $msg, $from);
                echo 'Mensagem enviada com sucesso';
            } catch (Exception $e) {
                // Lidar com erros durante o envio da mensagem
                // Aqui você pode registrar o erro, notificar o usuário, etc.
                echo 'Erro ao enviar mensagem: ' . $e->getMessage();
            }
        }
        $this->salvarMensagemNoBanco($msg);
        echo 'Mensagem salva no banco \n\n';
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

    private function conversasDoUsuario($id_user) { // OK
        try {
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
    
            $queryConvUser->bindParam(':id_user', $id_user);
            $queryConvUser->execute();
            
            $resultConvs = $queryConvUser->fetchAll(PDO::FETCH_COLUMN, 0);
    
            return $resultConvs;
        } catch (Exception $e) {
            // Erro ao lidar com as conversas do usuário
            echo 'Erro ao lidar com as conversas do usuário ' . $e->getMessage();
        }
    }

    private function mandarMensagemConversa($conversationId, $message, $from) { // OK
        try{ 
            if ($this->verificaçãoUsuarioConversa($conversationId, $from)) { // Verificar se o usuario tem permissão de mandar mensagem

                $participant = $this->participanteConversa($conversationId, $from); // Obtenha o outro usuario conectado a conversa
        
                $participantConnection = $this->ConexaoUsuario($participant); // Verificar se o o outro usuario está conectado(online)
                
                // Enviar se estiver conectado
                if ($participantConnection !== null) {
                    $participantConnection->send($message);
                }
            }
        } catch (Exception $e) {
            echo 'Erro ao lidar com as conversas do usuario' . $e->getMessage();
        }
    }

    private function verificaçãoUsuarioConversa($conversationId, $from) {  //OK
        try {
            // Verifique se o usuário atual está autorizado a enviar mensagens para esta conversa
            $userId = $this->pegarIdUsusarioConectado($from);
        
            // Consultando o banco de dados para verificar se o usuário está associado à conversa.
            $dbConnection = new dbConnection();
            $conn = $dbConnection->getConnect();
        
            $stmt = $conn->prepare("SELECT COUNT(*) AS autorizado FROM participante_conversa WHERE id_conversa = :conversationId AND id_user = :userId");
            $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
        
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
            return $result['autorizado'] > 0;
        } catch (Exception $e) {
            // Erro ao verificar usuario e conversa
            echo 'Erro ao verificar usuario e conversa ' . $e->getMessage();
        }
    }


    private function participanteConversa($conversationId, $from) { // OK
        try {
            // Obter a lista de todos os participantes da conversa com base no ID da conversa.
            // Consulte a tabela 'participante_conversa' para obter a lista de participantes.
            $userId = $this->pegarIdUsusarioConectado($from);

            $dbConnection = new dbConnection();
            $conn = $dbConnection->getConnect();

            $query = "SELECT id_user FROM participante_conversa WHERE id_conversa = :conversationId AND id_user != :id_user LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
            $stmt->bindParam(':id_user', $userId);
            $stmt->execute();

            $participant = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($participant) {
                return $participant['id_user'];
            } else {
                return null;
            }
        } catch (Exception $e) {
            // Erro ao carregar participantes da conversa
            echo ' Erro ao carregar participantes da conversa ' . $e->getMessage();
        }
    }

    private function ConexaoUsuario($userId) {
        try {
            // Obtenha a conexão WebSocket do usuário com base no seu ID.
            // Você pode percorrer a lista de conexões $this->cliente para encontrar a conexão correta.
    
            foreach ($this->cliente as $conn) {
                // Verifique se a conexão está associada ao usuário com o ID correspondente.
                if ($this->pegarIdUsusarioConectado($conn) == $userId) {
                    return $conn;
                }
            }
            return null; // Retorna null se o usuário não estiver online.
        } catch (Exception $e) {
            // Lidar com erros ao verificar o ID do usuário e a conexão
            echo 'Erro ao verificar o ID do usuário e a conexão: ' . $e->getMessage();
        }
    }    

    private function pegarIdUsusarioConectado(ConnectionInterface $conn) {  //OK
        try {
            $query = $conn->httpRequest->getUri()->getQuery();
            $id_user = str_replace("id_user=", "", $query);
    
            if (!empty($id_user)) {
                return $id_user;
            }
            
            return null;
        } catch (Exception $e) {
            echo 'Erro ao recuperar o ID do usuário da conexão WebSocket: ' . $e->getMessage();
            return null;
        }
    }

    private function salvarMensagemNoBanco($mensagem) { // OK
        try {    
            $dbConnection = new dbConnection();
            $conn = $dbConnection->getConnect();
            
            $mensagemArray = json_decode($mensagem, true);

            $addMensagem = $conn->prepare("INSERT INTO mensagens(mensagem_text, id_user, id_conversa) 
            VALUES (:mensagem, :id_user, :id_conversa)");

            $addMensagem->bindParam(':mensagem', $mensagemArray['mensagem']);
            $addMensagem->bindParam(':id_user', $mensagemArray['id_user']);
            $addMensagem->bindParam(':id_conversa', $mensagemArray['id_conversa']);

            $addMensagem->execute();
        } catch (Exception $e) {
            echo 'Erro ao salvar mensagem no banco de daodos: ' . $e->getMessage();
        }
    }
}