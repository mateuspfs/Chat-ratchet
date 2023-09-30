<?php

// require_once 'api/vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// $db_host = $_ENV['db_host'];
// $db_name = $_ENV['db_name'];
// $db_user = $_ENV['db_user'];
// $db_pass = $_ENV['db_pass'];

$db_host = 'localhost';
$db_name = 'db_chat';
$db_user = 'root';
$db_pass = 1525;

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
  } catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
  }