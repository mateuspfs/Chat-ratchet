<?php

    require_once 'config/connection.php';

    $usuario = $_POST['usuario'];
    $senha = $_POST['passw'];
    $n = 1;

    if($n == 1) { 
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :nome AND passw = :senha");
        $stmt->bindValue(':nome', $usuario);
        $stmt->bindValue(':senha', $senha);
        $stmt->execute();
        
        $user = $stmt->fetch();
        echo $user['username'];

    //     if (!isset($stmt)) {
    //         $error = $conn->errorInfo();
    //         echo "Erro ao executar a consulta: " . $error;
    //     } else {
    //         if ($stmt->rowCount() == 1) {
    //             $_SESSION['usuario'] = $dados['usuario'];
    //             header('Location: chat.php');
    //         } else {
    //             echo 'Usuário ou senha incorreto!';
    //         }
    //     }
    }

    
    //     $user = 'mateus';
    //     $pass = 1525;

    //     if($dados['usuario'] == $user && $dados['passw'] == $pass){
    //         $_SESSION['usuario'] = $dados['usuario'];
    //         header('Location: chat.php');
    //     } else {
    //         echo 'erro';
    //     }
    // }
?>