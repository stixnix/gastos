<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Compras</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <br>
    <div class="container">
        <h2>Registrar Nova Compra</h2>

        <!-- Área para exibir mensagens de confirmação -->
        <?php if (isset($mensagem)) { ?>
            <div class="mensagem">
                <?php echo $mensagem; ?>
            </div>
        <?php } ?>

        <form action="processa_registro.php" method="POST">
            <label for="data_compra">Data da Compra:</label>
            <input type="date" id="data_compra" name="data_compra" required>

            <label for="descricao">Descrição:</label>
            <input type="text" id="descricao" name="descricao" required>

            <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria" required>
                <?php
                include 'conexao.php';

                $sql = "SELECT id, nome FROM categorias";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
                    }
                } else {
                    echo '<option value="">Nenhuma categoria encontrada</option>';
                }

                $conn->close();
                ?>
            </select>
            <a href="gerenciar_categorias.php">Cadastrar categorias</a>

            <label for="valor">Valor:</label>
            <input type="number" id="valor" name="valor" step="0.01" required >

            <label for="forma_pagamento">Forma de Pagamento:</label>
            <select id="forma_pagamento" name="forma_pagamento" required>
                <?php
                include 'conexao.php';

                $sql = "SELECT id, nome FROM formas_pagamento";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
                    }
                } else {
                    echo '<option value="">Nenhuma forma de pagamento encontrada</option>';
                }

                $conn->close();
                ?>
            </select>
            <a href="gerenciar_pagamentos.php">Cadastrar formas de pagamento</a>
           <br>
            <label for="parcelas">Número de Parcelas:</label>
            <input type="number" id="parcelas" name="parcelas" min="1">

            <button type="submit">Registrar Compra</button>
        </form>

        <!-- Botão para ir para a tela de histórico de compras -->
        <form action="historico_compras.php" method="get" style="margin-top: 20px;">
            <button type="submit">Ver Histórico de Compras</button>
        </form>
    </div>
</body>
</html>
