<?php


try {
    require_once 'api/connection.php';

    $limit = 10;

    $offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_INT);

    $querry_msgns = "SELECT msg.id_msg, msg.mensagem_text, msg.id_user, user.nome 
                                FROM mensagens AS msg 
                                INNER JOIN usuarios AS user 
                                ON user.id_user = msg.id_user
                                -- WHERE msg.id_msg = 100
                                ORDER BY msg.id_msg DESC
                                LIMIT :limit
                                OFFSET :offset";

    $results_msgs = $conn->prepare($querry_msgns);
    $results_msgs->bindParam(':limit', $limit, PDO::PARAM_INT);
    $results_msgs->bindParam(':offset', $offset, PDO::PARAM_INT);

    $results_msgs->execute();

    $qtd_msgns = $results_msgs->rowCount();

    $querry_total_msgns = $conn->prepare("SELECT count(id_msg) AS total_mensagens FROM mensagens");
    $querry_total_msgns->execute();

    $result_total_msg = $querry_total_msgns->fetch(PDO::FETCH_ASSOC);

    if (($results_msgs) && ($results_msgs->rowCount() != 0)) {
        $dados = $results_msgs->fetchAll(PDO::FETCH_ASSOC);

        $retorno = ['status' => true, 'dados' => $dados, 'qtd_msg' => $qtd_msgns, "qt_total_msg" => $result_total_msg['total_mensagens']];
    } else {
        // Criar array de retorno
        $retorno = ['status' => false, 'msg' => "Isso é tudo, todas as mensagens foram carregadas"];
    }
} 
catch (PDOException $e) {
    // Tratar exceções PDO (erros de banco de dados)
    $retorno = ['status' => false, 'msg' => 'Erro no banco de dados: ' . $e->getMessage()];
} 
catch (Exception $e) {
    // Tratar outras exceções
    $retorno = ['status' => false, 'msg' => 'Erro: ' . $e->getMessage()];
}

// Retorna o resultado como JSON
echo json_encode($retorno);

