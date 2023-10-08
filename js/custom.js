 // recuperar o id que deve receber as msgs do chat
 const mensagemChat = document.getElementById('mensagem-chat');
        
 // endereço websocket
 const ws = new WebSocket('ws://localhost:8080');

 //realizar a conexão websocket
 ws.onopen = (e) => {
     console.log('Conectado');
 }

 ws.onmessage = (mensagemRecebida) => {

     // ler a mensagem enviada
     let resultado = JSON.parse(mensagemRecebida.data);

     // enviar a mensagem para o html, inserindo no final
     mensagemChat.insertAdjacentHTML('beforeend', `${resultado.nome}:${resultado.mensagem} <br>`);
 }

 const enviar = () =>{
     // recuperar mensagem
     let mensagem = document.getElementById("mensagem");

     // recuperar nome usuario
     let usuario = document.getElementById("usuario").textContent;

    // recuperar id do usuario
     let idUser = document.getElementById("id_user").value;

     // criar array de dados para enviar
     let dados = {
         mensagem: `${mensagem.value}`,
         id_user: idUser,
         nome: usuario
     }

     // enviar a mensagem para websocket
     ws.send(JSON.stringify(dados));

     // enviar a mensagem para o html, inserindo no final
     mensagemChat.insertAdjacentHTML('beforeend', `${usuario}: ${mensagem.value} <br>`);

     // limpa o campo
     mensagem.value = '';
 }

// Quantidade de mensagens carregados
var offset = 0;

// // Mover o scroll para o final
// var roleFinal = true;

// function verificarScroll(){
//     var chatBox = document.getElementById("chat-box");

//     if(chatBox.scrollTop <=10) {
//     console.log("usuario está proximo ao topo");

//     carregarMsg();
//     }
// }

// chatBox.addeEventListener('scroll', verificarScroll);


// Carregar mensagens do banco de dados
async function carregarMsg() {

    // chamar o php para carregar as mensagens do banco
    var dados = await fetch(`listar_msg.php?offset=${offset}`);
    
    var resposta = await dados.json();
    console.log(resposta);

    if(resposta['status']){

            //ler as msg e enviar pro chat
            resposta['dados'].map(item => { 
 
            // recuperar id usuario 
            var idUser = document.getElementById('id_user').value;

            if(idUser == item.id_user){
                mensagemChat.insertAdjacentHTML('afterbegin', `${item.nome}: ${item.mensagem_text} <br>`);
            } else {
                mensagemChat.insertAdjacentHTML('afterbegin', `${item.nome}: ${item.mensagem_text} <br>`);
            }
        });
    } else {
        //envia a mensagem para o html
        mensagemChat.insertAdjacentHTML('afterbegin', `<p style= 'color:red;'>${resposta['msg']}</p>`);
    }
 }

 // carregar as mensagens inicialmente
 carregarMsg();