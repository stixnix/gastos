<?php
include 'conexao.php';

// Definir a configuração local para português
setlocale(LC_TIME, 'pt_BR.UTF-8', 'portuguese');

// Inicializar a variável de mensagem
$mensagem = '';

// Definir o mês e ano atuais ou obtê-los a partir da URL
$mes_atual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_atual = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Calcular o mês e ano anterior e seguinte
$data_atual = DateTime::createFromFormat('Y-m', "$ano_atual-$mes_atual");

// Verificar se $data_atual foi inicializado corretamente
if ($data_atual === false) {
    // Caso não, inicializar com o mês e ano atuais
    $data_atual = new DateTime();
}

// Criar novos objetos DateTime para mês anterior e seguinte
$data_anterior = (new DateTime($data_atual->format('Y-m')))->modify('-1 month');
$data_seguinte = (new DateTime($data_atual->format('Y-m')))->modify('+1 month');

$mes_anterior = $data_anterior->format('m');
$ano_anterior = $data_anterior->format('Y');
$mes_seguinte = $data_seguinte->format('m');
$ano_seguinte = $data_seguinte->format('Y');

// Função para verificar se há registros em um determinado mês/ano
function tem_registros($conn, $mes, $ano) {
    $sql_check = "
    SELECT 1
    FROM registro_compras rc
    LEFT JOIN parcelas_compras pc ON rc.id = pc.registro_compra_id
    WHERE (MONTH(pc.mes_parcela) = ? AND YEAR(pc.mes_parcela) = ?)
       OR (pc.mes_parcela IS NULL AND MONTH(rc.data_compra) = ? AND YEAR(rc.data_compra) = ?)
    LIMIT 1";
    
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("iiii", $mes, $ano, $mes, $ano);
    $stmt_check->execute();
    $stmt_check->store_result();
    $has_records = $stmt_check->num_rows > 0;
    $stmt_check->close();

    return $has_records;
}

// Verificar se há registros no mês anterior e seguinte
$tem_registros_anterior = tem_registros($conn, $mes_anterior, $ano_anterior);
$tem_registros_seguinte = tem_registros($conn, $mes_seguinte, $ano_seguinte);

// Buscar compras e parcelas do banco de dados para o mês atual
$sql = "
SELECT rc.id, rc.descricao, rc.data_compra,
       IF(rc.parcelas > 1, pc.valor_parcela, rc.valor) AS valor_exibido,
       rc.parcelas, pc.numero_parcela, pc.mes_parcela,
       cat.nome AS categoria, fp.nome AS forma_pagamento
FROM registro_compras rc
LEFT JOIN parcelas_compras pc ON rc.id = pc.registro_compra_id
LEFT JOIN categorias cat ON rc.categoria_id = cat.id
LEFT JOIN formas_pagamento fp ON rc.forma_pagamento_id = fp.id
WHERE (MONTH(pc.mes_parcela) = ? AND YEAR(pc.mes_parcela) = ?)
   OR (pc.mes_parcela IS NULL AND MONTH(rc.data_compra) = ? AND YEAR(rc.data_compra) = ?)
ORDER BY rc.data_compra ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $mes_atual, $ano_atual, $mes_atual, $ano_atual);
$stmt->execute();
$result = $stmt->get_result();

// Array para armazenar compras do mês atual
$historico = [];

// Processar resultados
while ($row = $result->fetch_assoc()) {
    $historico[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Histórico de Compras</title>
    <link rel="stylesheet" href="historico_compras.css"> <!-- Referência ao CSS separado -->
</head>
<body>
    <div class="container">
        <h2>Histórico de Compras - <?php echo strftime('%B de %Y', strtotime("$ano_atual-$mes_atual")); ?></h2>

        <!-- Mensagem de confirmação -->
        <?php if ($mensagem): ?>
            <p class="mensagem"><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <!-- Navegação entre meses -->
        <div class="navigation">
            <?php if ($tem_registros_anterior): ?>
                <a href="?mes=<?php echo $mes_anterior; ?>&ano=<?php echo $ano_anterior; ?>">&#9664; Mês Anterior</a>
            <?php else: ?>
                <span class="disabled">&#9664; Mês Anterior</span>
            <?php endif; ?>

            <?php if ($tem_registros_seguinte): ?>
                <a href="?mes=<?php echo $mes_seguinte; ?>&ano=<?php echo $ano_seguinte; ?>">Próximo Mês &#9654;</a>
            <?php else: ?>
                <span class="disabled">Próximo Mês &#9654;</span>
            <?php endif; ?>
        </div>

        <!-- Tabela para o mês atual -->
        <?php if (count($historico) > 0): ?>
            <table class="historico-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Parcela</th>
                        <th>Forma de Pagamento</th>
                        <th>Categoria</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_mes = 0;
                    foreach ($historico as $compra): 
                        $total_mes += $compra['valor_exibido'];
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($compra['mes_parcela'] ?? $compra['data_compra'])); ?></td>
                            <td><?php echo htmlspecialchars($compra['descricao']); ?></td>
                            <td><?php echo number_format($compra['valor_exibido'], 2, ',', '.'); ?></td>
                            <td>
                                <?php
                                if ($compra['parcelas'] > 1) {
                                    echo "Parcela " . $compra['numero_parcela'] . " de " . $compra['parcelas'];
                                } else {
                                    echo "À vista";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($compra['forma_pagamento']); ?></td>
                            <td><?php echo htmlspecialchars($compra['categoria']); ?></td>
                            <td>
                                <a href="editar_compra.php?id=<?php echo $compra['id']; ?>" class="btn-editar">Editar</a>
                                <a href="excluir_compra.php?id=<?php echo $compra['id']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta compra?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Linha para valor total do mês -->
                    <tr class="total-row">
                        <td colspan="3">Total do Mês</td>
                        <td colspan="4"><?php echo number_format($total_mes, 2, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum registro encontrado para este mês.</p>
        <?php endif; ?>

        <!-- Botão para voltar ao registro -->
        <a href="registro.php" class="btn-voltar">Voltar ao Registro</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
