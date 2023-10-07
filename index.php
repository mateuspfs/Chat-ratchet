<?php

use Api\WebSocket\dbConnection;

session_start();
ob_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<body>
    <h1>Acesse o chat</h1>

    <form method="POST" action="">
        <label>Nome: </label>
        <input type="text" name="usuario" placeholder="Digite seu nome...">

        <label>Senha: </label>
        <input type="password" name="senha" placeholder="Digite sua senha...">

        <input type="submit" name="acessar" value="acessar">
    </form>

</body>
</html>

<?php

    require_once 'api/connection.php';

    $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

    if(!empty($dados['acessar'])) { 
        $sql = $conn->prepare("SELECT * FROM usuarios WHERE nome = :nome AND senha = :senha");
        $sql->bindParam(':nome', $dados['usuario']);
        $sql->bindParam(':senha', $dados['senha']);
        $sql->execute();

        if ($sql->errorCode() !== '00000') {
            $error = $sql->errorInfo();
            echo "Erro ao executar a consulta: " . $error[2];
        } else {
            if ($sql->rowCount() == 1) {
                $usuario = $sql->fetch(PDO::FETCH_ASSOC);
                $_SESSION['id_user'] = $usuario['id_user'];
                $_SESSION['usuario'] = $usuario['nome'];
                header('Location: chat.php');
            } else {
                $_SESSION['msg'] = "<p style='color:#f00'>Usuário ou senha incorreto!</p>";
            }
        }
    }
        
    $conn = null;
?>