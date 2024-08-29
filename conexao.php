<?php
// conexao.php: arquivo para conectar ao banco de dados MySQL

// Definir os detalhes da conexão
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "yme";

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Agora a variável $conn está disponível para uso em outros scripts
?>
