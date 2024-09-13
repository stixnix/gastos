<?php
// Incluir o arquivo de conexão
include 'conexao.php';

// Inicializar a variável de mensagem
$mensagem = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Buscar o usuário no banco de dados
    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar se a senha está correta
        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido, redirecionar para registro.html
            header("Location: dashboard.php");
            exit;
        } else {
            $mensagem = "Senha incorreta.";
        }
    } else {
        $mensagem = "Usuário não encontrado.";
    }

    // Fechar a conexão
    $conn->close();
}

// Incluir o formulário de login novamente para exibir a mensagem
include 'login.html';
?>
