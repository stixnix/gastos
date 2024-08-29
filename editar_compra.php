<?php
include 'conexao.php';

// Verificar se um ID de compra foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Buscar os dados da compra para preencher o formulário
    $sql = "SELECT * FROM registro_compras WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $compra = $result->fetch_assoc();
    } else {
        echo "Compra não encontrada.";
        exit;
    }
}

// Atualizar a compra se o formulário for enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $data_compra = $_POST['data_compra'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $parcelas = $_POST['parcelas'];

    $sql = "UPDATE registro_compras SET data_compra = ?, descricao = ?, valor = ?, parcelas = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdii", $data_compra, $descricao, $valor, $parcelas, $id);

    if ($stmt->execute()) {
        // Redireciona para a tela de histórico de compras após sucesso
        header('Location: historico_compras.php');
        exit; // Encerra a execução do script
    } else {
        echo "Erro ao atualizar compra: " . $stmt->error;
    }
    
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Compra</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Editar Compra</h2>
        <form action="editar_compra.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $compra['id']; ?>">

            <label for="data_compra">Data da Compra:</label>
            <input type="date" id="data_compra" name="data_compra" value="<?php echo $compra['data_compra']; ?>" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" value="<?php echo htmlspecialchars($compra['descricao']); ?>" required>

            <label for="valor">Valor:</label>
            <input type="number" id="valor" name="valor" step="0.01" value="<?php echo $compra['valor']; ?>" required>

            <label for="parcelas">Número de Parcelas:</label>
            <input type="number" id="parcelas" name="parcelas" min="1" value="<?php echo $compra['parcelas']; ?>" required>

            <button type="submit">Salvar Alterações</button>
        </form>

        <!-- Botão para voltar ao histórico -->
        <a href="historico_compras.php" class="btn-voltar">Voltar ao Histórico</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
