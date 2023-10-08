<?php

session_start();

ob_start();

require_once 'verificacao.php';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
</head>
<body>
    <h1>Chat</h1>

    <a href="sair.php">Sair</a>

    <h3>Bem vindo <span id="usuario"><?php echo $_SESSION['usuario']?></span></h3>

    <div class="chat-box" id="chat-box">
    <label>Nova Mensagem:</label>
    <input type="text" name="mensagem" id="mensagem" placeholder="Digite a mensagem...">

    <input type="hidden" name="id_user" id="id_user" value="<?php echo $_SESSION['id_user']?>">

    <input type = "button" onclick="enviar()" value="Enviar"><br><br>

    <span id="mensagem-chat"></span>
    </div>
    <script src="js/custom.js"></script>
</body>
</html>