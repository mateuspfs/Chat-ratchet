<?php

echo "<h1> Bem vindo ao teste <h1>";

function conversasDoUsuario($id_user) {
    try {
        // Pega os IDs das conversas às quais o usuário está ligado
        include_once 'api/connection.php';

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

$resultado = conversasDoUsuario(1);

echo '<pre>';
print_r($resultado);
echo '</pre>';
?>

