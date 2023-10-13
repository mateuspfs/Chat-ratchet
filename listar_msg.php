<?php

try {
    require_once 'api/connection.php';

    $limit = 10;
    $offset = filter_input(INPUT_GET, 'offset', FILTER_SANITIZE_NUMBER_INT);
    $id_conversa = filter_input(INPUT_GET, 'id_conversa', FILTER_SANITIZE_NUMBER_INT);
    
    if ($id_conversa === null) {
        $retorno = ['status' => false, 'msg' => 'Parâmetro id_conversa ausente'];
    } else {
        $query = "SELECT msg.id_msg, msg.mensagem_text, msg.id_user, msg.data_registro, u.nome 
                    FROM mensagens AS msg 
                        INNER JOIN usuarios AS u
                            ON u.id_user = msg.id_user
                                WHERE msg.id_conversa = :id_conversa
                                ORDER BY msg.data_registro DESC 
                                LIMIT :limit
                                OFFSET :offset";

        $results_msgs = $conn->prepare($query);
        $results_msgs->bindParam(':limit', $limit, PDO::PARAM_INT);
        $results_msgs->bindParam(':offset', $offset, PDO::PARAM_INT);
        $results_msgs->bindParam(':id_conversa', $id_conversa, PDO::PARAM_INT);

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
            $retorno = ['status' => false, 'msg' => "Não foram encontradas mensagens para a sala especificada"];
        }
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

