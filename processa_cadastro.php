<?php
// Incluir o arquivo de conexão
include 'conexao.php';

// Inicializar a variável de mensagem
$mensagem = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $telefone = $_POST['telefone'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        $mensagem = "Erro: As senhas não coincidem.";
    } else {
        // Hash da senha
        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir os dados na tabela
        $sql = "INSERT INTO usuarios (nome, email, senha, telefone) VALUES ('$nome', '$email', '$senha_hashed', '$telefone')";

        if ($conn->query($sql) === TRUE) {
            // Redirecionar para a tela registro.php
            header("Location: registro.php");
            exit(); // Termina a execução do script após o redirecionamento
        } else {
            $mensagem = "Erro ao cadastrar: " . $conn->error;
        }

        // Fechar a conexão
        $conn->close();
    }
}

// Incluir o formulário novamente para exibir a mensagem
include 'cadastro.html';
?>
