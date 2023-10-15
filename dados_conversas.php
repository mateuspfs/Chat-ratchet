<?php
require_once 'verificacao.php';

try {
    require_once 'api/connection.php';

    $queryConvUser = $conn->prepare('SELECT c.id_conversa 
                                        FROM conversas as c
                                        INNER JOIN participante_conversa as pc 
                                            ON c.id_conversa = pc.id_conversa
                                        INNER JOIN usuarios as u 
                                            ON u.id_user = pc.id_user
                                        WHERE u.id_user = :id_user');

    $queryConvUser->bindParam(':id_user', $_SESSION['id_user']);
    $queryConvUser->execute();

    if ($queryConvUser && $queryConvUser->rowCount() != 0) {
        $result_idConversa = $queryConvUser->fetchAll(PDO::FETCH_ASSOC);

        $dados = [];

        foreach ($result_idConversa as $conversa) {
            $queryUserConv = $conn->prepare("SELECT pc.id_conversa as 'id_conversa', pc.id_user as 'id_user', u.nome as 'nome_user'
                                            FROM conversas as c
                                            INNER JOIN participante_conversa as pc 
                                                ON c.id_conversa = pc.id_conversa
                                            INNER JOIN usuarios as u 
                                                ON u.id_user = pc.id_user
                                            WHERE pc.id_conversa = :id_conversa
                                                AND pc.id_user != :id_user");

            $queryUserConv->bindParam(':id_conversa', $conversa['id_conversa']);
            $queryUserConv->bindParam(':id_user', $_SESSION['id_user']);
            $queryUserConv->execute();

            if ($queryUserConv && $queryUserConv->rowCount() != 0) {
                $users = $queryUserConv->fetchAll(PDO::FETCH_ASSOC);

                $dados[$conversa['id_conversa']] = $users;
            }
        }
        $retorno = ['dados' => $dados];
    } else {
        // Criar array de retorno
        $retorno = ['msg' => "O usuário não está associado a nenhuma conversa"];
    }
} catch (PDOException $e) {
    // Tratar exceções PDO (erros de banco de dados)
    $retorno = ['msg' => 'Erro no banco de dados: ' . $e->getMessage()];
} catch (Exception $e) {
    // Tratar outras exceções
    $retorno = ['msg' => 'Erro: ' . $e->getMessage()];
}

// Retorna o resultado como JSON
echo json_encode($retorno);


