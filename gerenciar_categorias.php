<?php
// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php';

// Inicializar mensagem de feedback
$mensagem = '';

// Adicionar uma nova categoria
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nova_categoria'])) {
    $nova_categoria = $_POST['nova_categoria'];
    $sql = "INSERT INTO categorias (nome) VALUES (?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $nova_categoria);

        if ($stmt->execute()) {
            $mensagem = "Categoria adicionada com sucesso!";
        } else {
            $mensagem = "Erro ao adicionar categoria: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensagem = "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Excluir uma categoria
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['excluir_id'])) {
    $excluir_id = $_POST['excluir_id'];
    $sql = "DELETE FROM categorias WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $excluir_id);

        if ($stmt->execute()) {
            $mensagem = "Categoria excluída com sucesso!";
        } else {
            $mensagem = "Erro ao excluir categoria: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensagem = "Erro ao preparar a consulta: " . $conn->error;
    }
}

// Buscar todas as categorias para exibir na tabela
$sql = "SELECT id, nome FROM categorias";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Categorias</title>
    <link rel="stylesheet" href="categorias.css">
</head>
<body>
    <div class="container">
        <h2>Criar Categorias</h2>
        
        <!-- Mensagem de feedback -->
        <?php if ($mensagem): ?>
            <p><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <!-- Formulário para adicionar nova categoria -->
        <form action="gerenciar_categorias.php" method="POST">
            <label for="nova_categoria">Nova Categoria:</label>
            <br>
            <br>
            <input type="text" id="nova_categoria" name="nova_categoria" required>
            <button type="submit">Adicionar categoria</button>
        </form>
          <br>
        <!-- Lista de categorias existentes -->
        <h3>Categorias Existentes</h3>
        <table>
            <tr>
                <th>Nome</th>
                <th>Ação</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                    <td>
                        <form action="gerenciar_categorias.php" method="POST" style="display:inline;">
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
