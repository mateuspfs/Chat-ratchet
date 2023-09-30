<?php

session_start();

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
    <h3>Bem vindo <span id="usuario"><?php echo $_SESSION['usuario']?></span></h3>
    <label>Nova Mensagem:</label>
    <input type="text" name="mensagem" id="mensagem" placeholder="Digite a mensagem...">

    <input type = "button" onclick="enviar()" value="Enviar"><br><br>

    <span id="mensagem-chat"></span>

    <script>
        
        // recuperar o id que deve receber as msgs do chat
        const mensagemChat = document.getElementById('mensagem-chat');
        
        // endereço websocket
        const ws = new WebSocket('ws://localhost:8080');

        //realizar a conexão websocket
        ws.onopen = (e) => {
            console.log('Conectado');
        }

        ws.onmessage = (mensagemRecebida) => {

            // ler a mensagme enviada
            let resultado = JSON.parse(mensagemRecebida.data);

            // enviar a mensagem para o html, inserindo no final
            mensagemChat.insertAdjacentHTML('beforeend', `${resultado.mensagem}`);
        }

        const enviar = () =>{
            // recuperar mensagem
            let mensagem = document.getElementById("mensagem");

            // recuperar nome usuario
            let usuario = document.getElementById("usuario").textContent;
           
            // criar array de dados para enviar
            let dados = {
                mensagem: `${usuario}: ${mensagem.value} <br>`
            }

            // enviar a mensagem para websocket
            ws.send(JSON.stringify(dados));

            // enviar a mensagem para o html, inserindo no final
            mensagemChat.insertAdjacentHTML('beforeend', `${usuario}: ${mensagem.value} <br>`);

            // limpa o campo
            mensagem.value = '';
        }
    </script>
</body>
</html>