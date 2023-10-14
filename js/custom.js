// recuperar o id que deve receber as msgs do chat
const mensagemChat = document.getElementById('mensagem-chat');
// endereço websocket
const ws = new WebSocket('ws://localhost:8080');
// Quantidade de mensagens carregadas
var offset = 0;
// ID da sala
var id_conversa = 1; // Defina o ID da sala apropriado


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

 const enviar = () => {
     // recuperar mensagem
    let mensagem = document.getElementById("mensagem");

     // recuperar nome usuario
    let usuario = document.getElementById("usuario").textContent;

    // recuperar id do usuario
    let idUser = document.getElementById("id_user").value;

    // recuperar id da conversa
     let idConversa = 1;
     
     // criar array de dados para enviar
     let dados = {
         mensagem: `${mensagem.value}`, 
         id_user: idUser,
         nome: usuario,
         id_conversa: idConversa
     }

     // enviar a mensagem para websocket
     ws.send(JSON.stringify(dados));

     // enviar a mensagem para o html, inserindo no final
     mensagemChat.insertAdjacentHTML('beforeend', `${usuario}: ${mensagem.value} <br>`);

     // limpa o campo
     mensagem.value = '';
 }


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


console.log("OFFSET:", offset, "Id da conversa", id_conversa);

// Função para carregar mensagens do banco de dados
async function carregarMsg() {
    // Chamar o PHP para carregar as mensagens do banco
    var dados = await fetch(`listar_msg.php?offset=${offset}&id_conversa=${id_conversa}`);

    var resposta = await dados.json();

    if (resposta.status) {
        resposta.dados.forEach(item => {
            var idUser = document.getElementById('id_user').value;
            var mensagem = `${item.nome}: ${item.mensagem_text} <br>`;
            mensagemChat.insertAdjacentHTML('afterbegin', idUser == item.id_user ? mensagem : mensagem);
        });
    } else {
        mensagemChat.insertAdjacentHTML('afterbegin', `<p style='color:red;'>${resposta.msg}</p>`);
    }
 }

// Carregar as mensagens inicialmente
carregarMsg();
