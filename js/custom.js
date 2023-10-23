
const userId = document.getElementById('id_user').value; // pegar o ID do usuário da sessão PHP

const mensagemChat = document.getElementById('mensagem-chat'); // recuperar o id que deve receber as msgs do chat

var offset = 0;

let idConversaAtual = null;

const ws = new WebSocket('ws://localhost:8080?id_user=' + userId);

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

    let dados = {
        mensagem: mensagem.value,
        id_user: userId,
        nome: usuario,
        id_conversa: idConversaAtual
    };

    // enviar a mensagem para websocket
    ws.send(JSON.stringify(dados));

    // enviar a mensagem para o html, inserindo no final
    mensagemChat.insertAdjacentHTML('beforeend', `${usuario}: ${mensagem.value} <br>`);

    // limpa o campo
    mensagem.value = '';
}

// Função para buscar os dados das conversas usando fetch
function buscarDadosConversas() {
    fetch('dados_conversas.php')
        .then(response => response.json())
        .then(data => {
            // Manipule os dados recebidos aqui
            if (data.dados) {
                // Dados das conversas foram recebidos com sucesso
                const conversas = data.dados;
                
                // Atualize a página com os dados das conversas
                atualizarPagina(conversas);
            } else {
                // Exibir uma mensagem de erro, se necessário
                console.error(data.msg);
            }
        })
        .catch(error => {
            console.error('Erro ao buscar os dados das conversas:', error);
        });
}

// Função para atualizar a página com os dados das conversas
function atualizarPagina(conversas) {
    // atualizar a lista de usuarios
    const userList = document.getElementById('user-list');
    
    // Limpe a lista de usuários antes de adicionar as novas conversas
    userList.innerHTML = '';

    for (const idConversa in conversas) {
        const conversa = conversas[idConversa];

        // Crie um elemento de conversa
        const conversaItem = document.createElement('li');
        conversaItem.className = 'conversa-item';
        conversaItem.setAttribute('data-conversa-id', idConversa);
        const primeiroUsuario = conversa[0];
        conversaItem.textContent = primeiroUsuario.nome_user;

        // Adicione a conversa à lista
        userList.appendChild(conversaItem);

        // Adicione eventos de clique para mostrar o chat quando a conversa for clicada
        conversaItem.addEventListener('click', () => {
        // Código para mostrar o chat da conversa correspondente
        mostrarChat(conversa, idConversa);
        });
    }
}

// Função para mostrar o chat com os usuários da conversa
function mostrarChat(usuarios, idConversa) {
    mensagemChat.innerHTML = '';
    idConversaAtual = idConversa;

    usuarios.forEach(usuario => {
        // mostarar o usuario e conectado a conversa e o id dele
        const nomeUsuario = usuario.nome_user;
        const idUsuario = usuario.id_user;

        const nome_user = document.getElementById('nome_usuario');
        nome_user.textContent = nomeUsuario;
        console.log(`Usuário: ${nomeUsuario} (ID: ${idUsuario})<br>`);
    });

    // Agora o chat está visível após o clique
    mensagemChat.style.display = 'block';
    carregarMsg(usuarios[0].id_conversa); 
}

// Função para carregar mensagens do banco de dados
async function carregarMsg(idConversa) {
    // Chamar o PHP para carregar as mensagens do banco
    var dados = await fetch(`listar_msg.php?offset=${offset}&id_conversa=${idConversa}`);

    var resposta = await dados.json();

    if (resposta.status) {
        resposta.dados.forEach(item => {
            var mensagem = `${item.nome}: ${item.mensagem_text} <br>`;
            mensagemChat.insertAdjacentHTML('afterbegin', userId == item.id_user ? mensagem : mensagem);
        });
    } else {
        mensagemChat.insertAdjacentHTML('afterbegin', `<p style='color:red;'>${resposta.msg}</p>`);
    }
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

// Chame a função para buscar os dados das conversas quando a página carregar

document.addEventListener('DOMContentLoaded', buscarDadosConversas);