<?php

require_once 'global.php';

$db_host = $_ENV['db_host'];
$db_name = $_ENV['db_name'];
$db_user = $_ENV['db_user'];
$db_pass = $_ENV['db_pass'];

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  } catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
  }
  