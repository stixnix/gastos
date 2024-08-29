<?php
// Incluir o arquivo de conexão com o banco de dados
include 'conexao.php';

// Inicializar a variável de mensagem
$mensagem = '';

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Receber os dados do formulário
    $data_compra = $_POST['data_compra'];
    $descricao = $_POST['descricao'];
    $categoria_id = $_POST['categoria'];
    $valor_total = $_POST['valor']; // Valor total da compra
    $forma_pagamento_id = $_POST['forma_pagamento'];
    $parcelas = $_POST['parcelas'] ? $_POST['parcelas'] : 1; // Definir parcelas como 1 se estiver vazio
    $local_compra = $_POST['local_compra'];
    $observacoes = $_POST['observacoes'];

    // Verificar se o número de parcelas é maior que 1
    if ($parcelas > 1) {
        $valor_parcela = $valor_total / $parcelas; // Calcular o valor de cada parcela
    } else {
        $valor_parcela = $valor_total; // Valor total se houver apenas uma parcela
    }

    // Inserir a compra na tabela registro_compras, incluindo o valor de cada parcela
    $sql = "INSERT INTO registro_compras (data_compra, descricao, categoria_id, valor, valor_parcela, forma_pagamento_id, parcelas, local_compra, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssisdisss", $data_compra, $descricao, $categoria_id, $valor_total, $valor_parcela, $forma_pagamento_id, $parcelas, $local_compra, $observacoes);

        // Executar a consulta
        if ($stmt->execute()) {
            $compra_id = $stmt->insert_id; // Obter o ID da compra recém-inserida

            // Inserir cada parcela na tabela parcelas_compras apenas se o número de parcelas for maior que 1
            if ($parcelas > 1) {
                for ($i = 1; $i <= $parcelas; $i++) {
                    // Ajustar para que a primeira parcela seja no mês da compra
                    $mes_parcela = date('Y-m-d', strtotime("+".($i - 1)." month", strtotime($data_compra)));
                    
                    $sql_parcela = "INSERT INTO parcelas_compras (registro_compra_id, numero_parcela, mes_parcela, valor_parcela) 
                                    VALUES (?, ?, ?, ?)";
                    
                    if ($stmt_parcela = $conn->prepare($sql_parcela)) {
                        $stmt_parcela->bind_param("iisd", $compra_id, $i, $mes_parcela, $valor_parcela);
                        $stmt_parcela->execute();
                        $stmt_parcela->close();
                    } else {
                        $mensagem = "Erro ao preparar consulta de parcela: " . $conn->error;
                        break;
                    }
                }
            }

            $mensagem = "Registro de compra salvo com sucesso!";
        } else {
            $mensagem = "Erro ao salvar o registro de compra: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensagem = "Erro ao preparar a consulta: " . $conn->error;
    }

    $conn->close();
}

// Incluir o formulário novamente para exibir a mensagem
include 'registro.php';
?>
