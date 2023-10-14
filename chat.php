<?php

require 'teste.php';

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

                <?php foreach($dados as $conversas) {?>
                
                <li class="conversa-item" data-conversa-id="<?php echo $conversas['id_conversa']; ?>">
                Conversa <?php echo $conversas['id_conversa']; ?>
    
                <li class="user-item" data-user-id="<?php echo $conversas['id_user']; ?>">Usuário <?php echo $conversas['id_user']; ?></li>
                
                <?php } ?>
            </ul>
        </div>
        <div class="chat-box" id="chat-box">
            <div class="chat-header">
                <h2>Conversa com <span id="<?php echo $conversas['id_user']; ?>"></span></h2>
            </div>
            <div class="chat-messages" id="chat-messages">
                <!-- Aqui você exibirá as mensagens da conversa -->
            </div>
            <div class="message-input">
                <input type="text" name="mensagem" id="mensagem" placeholder="Digite a mensagem...">
                <input type="hidden" name="id_user" id="id_user" value="<?php echo $_SESSION['id_user']?>">
                <input type="button" onclick="enviar()" value="Enviar">
            </div>
        </div>
    </div>

    <script src="js/custom.js"></script>
</body>
</html>


<!-- <!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
</head>
<body>
    <h1>Chat</h1>

    <a href="sair.php">Sair</a>
    
    <h3>Bem vindo <span id="usuario"><?php // echo $_SESSION['usuario']?></span></h3>

    <div class="chat-box" id="chat-box">

    <label>Nova Mensagem:</label>
    <input type="text" name="mensagem" id="mensagem" placeholder="Digite a mensagem...">

    <input type="hidden" name="id_user" id="id_user" value="<?php // echo $_SESSION['id_user']?>">

    <input type = "button" onclick="enviar()" value="Enviar"><br><br>

    <span id="mensagem-chat"></span>

    </div>

    <script src="js/custom.js"></script>
</body>
</html> -->