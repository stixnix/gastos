<?php
// Exibir todos os erros para facilitar a depuração
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php';

// Verificar se a conexão foi estabelecida corretamente
if (!$conn) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
} else {
    echo "Conexão com o banco de dados estabelecida com sucesso.<br>";
}

// Verificar se o formulário foi enviado via método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verificar se as senhas coincidem
    if ($senha !== $confirmar_senha) {
        echo "As senhas não coincidem!";
    } else {
        // Hash da senha para segurança
        $senha_hashed = password_hash($senha, PASSWORD_DEFAULT);

        // Preparar a consulta SQL para inserir os dados
        $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";

        // Preparar a declaração
        if ($stmt = $conn->prepare($sql)) {
            // Associar os parâmetros e executar a consulta
            $stmt->bind_param("sss", $nome, $email, $senha_hashed);
            
            if ($stmt->execute()) {
                // Redirecionar para a página de login em caso de sucesso
                header("Location: login.html");
                exit();
            } else {
                echo "Erro ao cadastrar: " . $stmt->error;
            }

            // Fechar a declaração
            $stmt->close();
        } else {
            echo "Erro ao preparar a consulta: " . $conn->error;
        }
    }

    // Fechar a conexão
    $conn->close();
}
?>
