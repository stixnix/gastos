<?php
include 'conexao.php';

// Definir a configuração local para português
setlocale(LC_TIME, 'pt_BR.UTF-8', 'portuguese');

// Inicializar a variável de mensagem (se necessário)
$mensagem = '';

// Definir o mês e ano atuais ou obtê-los a partir da URL
$mes_atual = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$ano_atual = isset($_GET['ano']) ? $_GET['ano'] : date('Y');

// Função para obter o nome do mês por extenso
function nomeMes($mes) {
    $meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
    return $meses[$mes - 1];
}

// Buscar o total de despesas e gastos do mês atual
$sql_total_mes = "
SELECT SUM(IF(rc.parcelas = 1, rc.valor, pc.valor_parcela)) AS total_mes
FROM registro_compras rc
LEFT JOIN parcelas_compras pc ON rc.id = pc.registro_compra_id
WHERE 
    (rc.parcelas = 1 AND MONTH(rc.data_compra) = ? AND YEAR(rc.data_compra) = ?)
    OR 
    (rc.parcelas > 1 AND MONTH(pc.mes_parcela) = ? AND YEAR(pc.mes_parcela) = ?)";
$stmt_total_mes = $conn->prepare($sql_total_mes);
$stmt_total_mes->bind_param("iiii", $mes_atual, $ano_atual, $mes_atual, $ano_atual);
$stmt_total_mes->execute();
$result_total_mes = $stmt_total_mes->get_result();
$total_mes = $result_total_mes->fetch_assoc()['total_mes'] ?? 0;
$stmt_total_mes->close();

// Buscar gastos por categoria
$sql_categorias = "
SELECT cat.nome AS categoria, SUM(IF(rc.parcelas = 1, rc.valor, pc.valor_parcela)) AS total_categoria
FROM registro_compras rc
LEFT JOIN parcelas_compras pc ON rc.id = pc.registro_compra_id
LEFT JOIN categorias cat ON rc.categoria_id = cat.id
WHERE 
    (rc.parcelas = 1 AND MONTH(rc.data_compra) = ? AND YEAR(rc.data_compra) = ?)
    OR 
    (rc.parcelas > 1 AND MONTH(pc.mes_parcela) = ? AND YEAR(pc.mes_parcela) = ?)
GROUP BY cat.nome";
$stmt_categorias = $conn->prepare($sql_categorias);
$stmt_categorias->bind_param("iiii", $mes_atual, $ano_atual, $mes_atual, $ano_atual);
$stmt_categorias->execute();
$result_categorias = $stmt_categorias->get_result();

// Buscar gastos por forma de pagamento
$sql_pagamento = "
SELECT fp.nome AS forma_pagamento, SUM(IF(rc.parcelas = 1, rc.valor, pc.valor_parcela)) AS total_pagamento
FROM registro_compras rc
LEFT JOIN parcelas_compras pc ON rc.id = pc.registro_compra_id
LEFT JOIN formas_pagamento fp ON rc.forma_pagamento_id = fp.id
WHERE 
    (rc.parcelas = 1 AND MONTH(rc.data_compra) = ? AND YEAR(rc.data_compra) = ?)
    OR 
    (rc.parcelas > 1 AND MONTH(pc.mes_parcela) = ? AND YEAR(pc.mes_parcela) = ?)
GROUP BY fp.nome";
$stmt_pagamento = $conn->prepare($sql_pagamento);
$stmt_pagamento->bind_param("iiii", $mes_atual, $ano_atual, $mes_atual, $ano_atual);
$stmt_pagamento->execute();
$result_pagamento = $stmt_pagamento->get_result();

// Fechar conexões
$stmt_categorias->close();
$stmt_pagamento->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumo de Gastos - <?php echo nomeMes($mes_atual) . " " . $ano_atual; ?></title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Menu lateral -->
        <div class="menu-lateral">
            <!-- Logo -->
            <div class="logo-container">
            <img src="src/imagem/5.png" alt="Logo YME" class="logo">
            </div>

            <h2>Menu</h2>
            <ul>
                <li><a href="registro.php">Registrar Nova Compra</a></li>
                <li><a href="historico_compras.php">Histórico de Compras</a></li>
                <li><a href="logout.php" class="logout-button">Sair</a></li> <!-- Botão de Logout -->
            </ul>
        </div>

        <!-- Conteúdo principal -->
        <div class="content">
            <h1>Resumo de Gastos - <?php echo nomeMes($mes_atual) . " " . $ano_atual; ?></h1>

            <!-- Total Gasto no Mês -->
            <div class="total-mes-container">
                <h3>Total Gasto no Mês: R$ <?php echo number_format($total_mes, 2, ',', '.'); ?></h3>
            </div>

            <!-- Containers Flexíveis para Gastos por Categoria e Forma de Pagamento -->
            <div class="container-flex">
                <!-- Gastos por Categoria -->
                <div class="flex-item gastos-categoria">
                    <h2>Gastos por categoria</h2>
                    <?php while ($row = $result_categorias->fetch_assoc()): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($row['categoria']); ?></h3>
                            <p>Total: R$ <?php echo number_format($row['total_categoria'], 2, ',', '.'); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Gastos por Forma de Pagamento -->
                <div class="flex-item gastos-pagamento">
                    <h2>Gastos por forma de pagamento</h2>
                    <?php while ($row = $result_pagamento->fetch_assoc()): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($row['forma_pagamento']); ?></h3>
                            <p>Total: R$ <?php echo number_format($row['total_pagamento'], 2, ',', '.'); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
