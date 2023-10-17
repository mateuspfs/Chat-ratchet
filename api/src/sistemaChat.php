<?php

namespace Api\WebSocket;

use DateTime;
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

    public function onOpen(ConnectionInterface $conn) 
    {
        try {
            // Obtenha as conversas às quais o usuário pertence a partir do banco de dados
            $conversations = $this->conversasDoUsuario($conn);

            // Associe o usuário às conversas
            foreach ($conversations as $conversation) {
                $this->userConversations[$conn->resourceId][] = $conversation['id_conversa'];
            }

            // Adicionar o cliente na lista
            $this->cliente->attach($conn);

            echo "Nova conexão: {$conn->resourceId} \n\n";
        } catch (Exception $e) {
            // Lidar com erros ao abrir a conexão
            echo 'Erro ao salvar mensagem no banco de dados: ' . $e->getMessage();
        }
    }

    // Abrir conexão para novo cliente
    // public function onOpen(ConnectionInterface $conn) {
    //     $id_user = $_GET['user'] ?? null;
        
    //     if ($id_user !== null) {
    //         // Associe a conexão WebSocket ao id_user
    //         $this->userConnections[$id_user] = $conn;
    
    //         // Obtenha as conversas às quais o usuário pertence a partir do banco de dados
    //         $conversations = $this->conversasDoUsuario($conn);
    
    //         // Associe o usuário às conversas
    //         foreach ($conversations as $conversation) {
    //             $this->userConversations[$conn->resourceId][] = $conversation['id_conversa'];
    //         }
    
    //         // Adicionar o cliente na lista
    //         $this->cliente->attach($conn);
    
    //         echo "Nova conexão para o usuário {$id_user} ({$conn->resourceId})\n\n";
    //     } else {
    //         // Feche a conexão se o id_user não for fornecido
    //         $conn->close();
    //     }
    // }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        // Verificar se o usuário está autorizado a enviar mensagens para a conversa
        $userConversations = $this->userConversations[$from->resourceId] ?? [];
    
        // Itere sobre as conversas do usuário e envie a mensagem para os participantes de cada conversa
        foreach ($userConversations as $conversationId) {
            try {
                // Envie a mensagem para os participantes desta conversa
                $this->mandarMensagemConversa($conversationId, $msg, $from);
            } catch (Exception $e) {
                // Lidar com erros durante o envio da mensagem
                // Aqui você pode registrar o erro, notificar o usuário, etc.
                echo 'Erro ao enviar mensagem: ' . $e->getMessage();
            }
        }
    
        try {
            $this->salvarMensagemNoBanco($msg);
        } catch (Exception $e) {
            // Lidar com erros durante o salvamento da mensagem no banco de dados
            // Aqui você pode registrar o erro, notificar o usuário, etc.
            echo 'Erro ao salvar mensagem no banco de dados: ' . $e->getMessage();
        }
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
        try{
            // Pega os IDs das conversas às quais o usuário está ligado 
            $dbConnection = new dbConnection();
            $conn = $dbConnection->getConnect();
            $queryConvUser = $conn->prepare('SELECT c.id_conversa, u.id_user
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
        } catch (Exception $e) {
            // Erro ao lidar com as conversas do usuario
            echo 'Erro ao lidar com as conversas do usuario ' . $e->getMessage();
        }
    }

    private function mandarMensagemConversa($conversationId, $message, $from) {
        try{
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
        } catch (Exception $e) {
            // Erro ao lidar com as conversas do usuario
            echo 'Erro ao mandar mensagem ' . $e->getMessage();
        }
    }

    private function verificaçãoUsuarioConversa($conversationId, $from) {
        try {
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
        } catch (Exception $e) {
            // Erro ao verificar usuario e conversa
            echo 'Erro ao verificar usuario e conversa ' . $e->getMessage();
        }
    }


    private function participantesConversa($conversationId) {
        try {
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
        } catch (Exception $e) {
            // Erro ao carregar participantes da conversa
            echo ' Erro ao carregar participantes da conversa ' . $e->getMessage();
        }
    }

    private function userConnection($userId) {
        try {
            // Obtenha a conexão WebSocket do usuário com base no seu ID.
            // Você pode percorrer a lista de conexões $this->cliente para encontrar a conexão correta.

            foreach ($this->cliente as $conn) {
                // Verifique se a conexão está associada ao usuário com o ID correspondente.
                if ($this->idUserConnection($conn) == $userId) {
                    return $conn;
                }
            }
            return null; // Retorna null se o usuário não estiver online.
        } catch (Exception $e) {
            // Erro 
            echo 'Erro ao verificar o id do usuario e connection: ' . $e->getMessage();
        }
    }

    private function idUserConnection($userId) {
        try {
            // Obtenha o ID do usuário com base na conexão WebSocket usando a variável de sessão.
            if (isset($_SESSION['id_user'])) {
                $userId = $_SESSION['id_user'];

                // Verifique se a conexão está associada a um ID de usuário válido.
                if ($userId !== null && isset($this->userConnections[$userId])) {
                    return $userId;
                }
            }
            return null; // Retorna null se não encontrar uma associação válida.
        } catch (Exception $e) {
            // Erro ao lidar com as conversas do usuario
            echo 'Erro ao salvar mensagem no banco de dados: ' . $e->getMessage();
        }
    }

    private function salvarMensagemNoBanco($mensagem) {
        try {    
            $dbConnection = new dbConnection();
            $conn = $dbConnection->getConnect();
            
            $mensagemArray = json_decode($mensagem, true);

            $dataHoraAtual = new DateTime();
            // formatando para string
            $dataHoraFormatada = $dataHoraAtual->format('Y-m-d H:i:s');

            $addMensagem = $conn->prepare("INSERT INTO mensagens(mensagem_text, id_user, id_conversa, data_registro) 
            VALUES (:mensagem, :id_user, :id_conversa, :data_registro)");

            $addMensagem->bindParam(':mensagem', $mensagemArray['mensagem']);
            $addMensagem->bindParam(':id_user', $mensagemArray['id_user']);
            $addMensagem->bindParam(':id_conversa', $mensagemArray['id_conversa']);
            $addMensagem->bindParam(':data_registro', $dataHoraFormatada);

            $addMensagem->execute();
        } catch (Exception $e) {
            // Erro ao salvar mensagem no banco de daodos
            echo 'Erro ao salvar mensagem no banco de daodos: ' . $e->getMessage();
        }
    }
}