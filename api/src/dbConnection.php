<?php

namespace Api\WebSocket;

use PDO;
use PDOException;

require_once '../api/global.php';

class dbConnection 
{

    private string $host;
    private string $user;
    private string $pass;
    private string $db_name;
    private int|string $port;
    private object $connect;

    public function __construct()
    {
        $this->host = $_ENV['db_host'];
        $this->user = $_ENV['db_user'];
        $this->pass = $_ENV['db_pass'];
        $this->db_name = $_ENV['db_name'];
        $this->port = (int)$_ENV['db_port'];
        $this->connect = null; 
    }

    public function getConnect(): object
    {
        try {
            $this->connect = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                                      $this->user, $this->pass);       
            echo "Conexão realizada com sucesso";  
            return $this->connect;
            
          } catch (PDOException $err) {
            die("Erro na conexão com o banco de dados: " . $err->getMessage());
          }
    }
}

