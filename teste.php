<?php

echo "<h1> Bem vindo ao teste <h1>";

function verificaçãoUsuarioConversa($conversationId) {  //OK
    try {
        include 'api/connection.php';
        // Verifique se o usuário atual está autorizado a enviar mensagens para esta conversa
        $userId = 6;
    
    
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


$resultado = verificaçãoUsuarioConversa(6);

echo '<pre>';
print_r($resultado);
echo '</pre>';
?>

