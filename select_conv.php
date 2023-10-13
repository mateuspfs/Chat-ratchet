<?php 

session_start();
ob_start();
require 'verificacao.php';
require 'api/connection.php';

// $dados = filter_input_array(INPUT_POST, FILTER_DEFAULT);

// if(!empty($dados['user_conv'])){

// $user_conv = $_POST["user_conv"];

// $busca_user = $conn->prepare('SELECT id_user, nome FROM usuarios WHERE nome=:user_conv');
// $busca_user->bindParam(':user_conv', $user_conv);
// $busca_user->execute();

//     if($busca_user->errorCode() !== '00000'){
//         $error = $busca_user->errorInfo();
//         echo "Erro ao executar a consulta: " . $error[2];
//     } else {
//         if($busca_user->rowCount() >= 1){
//             $usuarios_conv = $busca_user->fetch();
            
//             $_SESSION['id_user_conv'] = $usuarios_conv['id_user'];
//             echo  $_SESSION['id_user_conv'];
//         }
//     }

// }

?>

 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>home</title>
 </head>
 <body>

    <form action="" method="POST">
    
    <label for="busca_user">Iniciar conversa</label>
    <input type="text" name="user_conv" placeholder="pesquise o usuário aqui">

    </form>

 </body>
 </html>

