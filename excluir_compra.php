<?php
// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php';

// Inicializar a variável de mensagem
$mensagem = '';

// Verificar se o ID da compra foi passado
if (isset($_GET['id'])) {
    $compra_id = $_GET['id'];

    // Primeiro, excluir as parcelas associadas
    $sql_excluir_parcelas = "DELETE FROM parcelas_compras WHERE registro_compra_id = ?";
    if ($stmt_parcela = $conn->prepare($sql_excluir_parcelas)) {
        $stmt_parcela->bind_param("i", $compra_id);
        $stmt_parcela->execute();
        $stmt_parcela->close();

        // Agora, excluir o registro de compra
        $sql_excluir_compra = "DELETE FROM registro_compras WHERE id = ?";
        if ($stmt_compra = $conn->prepare($sql_excluir_compra)) {
            $stmt_compra->bind_param("i", $compra_id);
            if ($stmt_compra->execute()) {
                $mensagem = "Compra e parcelas associadas excluídas com sucesso!";
                // Redirecionar para a página de histórico com a mensagem de sucesso
                header("Location: historico_compras.php?mensagem=" . urlencode($mensagem));
                exit();
            } else {
                $mensagem = "Erro ao excluir compra: " . $stmt_compra->error;
            }
            $stmt_compra->close();
        } else {
            $mensagem = "Erro ao preparar a consulta de exclusão de compra: " . $conn->error;
        }
    } else {
        $mensagem = "Erro ao preparar a consulta de exclusão de parcelas: " . $conn->error;
    }

    $conn->close();
} else {
    $mensagem = "ID de compra inválido.";
}

// Exibir a mensagem de erro, se houver
if (!empty($mensagem)) {
    echo $mensagem;
}
?>
