<?php

require_once 'verificacao.php';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" type="text/css" href="css/chat.css">
</head>
<body>
    <h1>Chat</h1>

    <a href="sair.php">Sair</a>
    
    <h3>Bem vindo <span id="usuario"><?php echo $_SESSION['usuario']?></span></h3>

    <div class="chat-container">

        <div class="user-list">
            <h2>Usuários</h2>
            <ul id="user-list">
                <!-- A lista de conversas será preenchida dinamicamente com JavaScript -->
            </ul>
        </div>

        <div class="chat-box" id="chat-box">

            <div class="chat-header">
                <h2>Conversa com <span id="nome_usuario"></span></h2>
            </div>

            <div class="mensagem-chat" id="mensagem-chat">
                <!-- Aqui exibirá as mensagens da conversa -->
            </div>

            <form class="message-chat" id="message-chat">
                <input type="text" name="mensagem" id="mensagem" placeholder="Digite a mensagem...">
                <input type="hidden" name="id_user" id="id_user" value="<?php echo $_SESSION['id_user']?>">
                <input type="button" onclick="enviar()" value="Enviar">
            </form>

        </div>
        
    </div>

    <script src="js/custom.js"></script>

</body>
</html>
