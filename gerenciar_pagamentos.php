<?php
// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php';

// Inicializar mensagem de feedback
$mensagem = '';

// Adicionar uma nova forma de pagamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nova_forma_pagamento'])) {
    $nova_forma_pagamento = $_POST['nova_forma_pagamento'];
    $sql = "INSERT INTO formas_pagamento (nome) VALUES (?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $nova_forma_pagamento);

        if ($stmt->execute()) {
            $mensagem = "Forma de pagamento adicionada com sucesso!";
        } else {
            $mensagem = "Erro ao adicionar forma de pagamento: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensagem = "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Excluir uma forma de pagamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_id'])) {
    $excluir_id = $_POST['excluir_id'];
    $sql = "DELETE FROM formas_pagamento WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $excluir_id);

        if ($stmt->execute()) {
            $mensagem = "Forma de pagamento excluída com sucesso!";
        } else {
            $mensagem = "Erro ao excluir forma de pagamento: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensagem = "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Buscar todas as formas de pagamento para exibir na tabela
$sql = "SELECT id, nome FROM formas_pagamento";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Formas de Pagamento</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Gerenciar Formas de Pagamento</h2>
        
        <!-- Mensagem de feedback -->
        <?php if ($mensagem): ?>
            <p><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <!-- Formulário para adicionar nova forma de pagamento -->
        <form action="gerenciar_pagamentos.php" method="POST">
            <label for="nova_forma_pagamento">Nova Forma de Pagamento:</label>
            <input type="text" id="nova_forma_pagamento" name="nova_forma_pagamento" required>
            <button type="submit">Adicionar Forma de Pagamento</button>
        </form>

        <!-- Lista de formas de pagamento existentes -->
        <h3>Formas de Pagamento Existentes</h3>
        <table>
            <tr>
                <th>Nome</th>
                <th>Ação</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td>
                        <form action="gerenciar_pagamentos.php" method="POST" style="display:inline;">
                            <input type="hidden" name="excluir_id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Botão para voltar ao registro.php -->
        <a href="registro.php" class="btn-voltar">Voltar ao Registro</a>

    </div>
</body>
</html>

<?php
$conn->close();
?>
