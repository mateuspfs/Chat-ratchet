<?php

session_start();
ob_start();

if(!isset($_SESSION['id_user'])){
    header('Location: index.php');
    $_SESSION['msg'] = "<p style='color:#f00'>Necesário estar logado para acessar a página!</p>";
}