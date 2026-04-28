<?php
// ============================================================
// ARQUIVO: admin/estoque.php
// FUNÇÃO: Controle de estoque com preços de custo e venda
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

// -----------------------------------------------
// PROCESSAMENTO POST
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $produto_id      = (int)($_POST['produto_id']      ?? 0);
    $quantidade      = (int)($_POST['quantidade']      ?? 0);
    $preco_fornecedor = (float)str_replace(',', '.', $_POST['preco_fornecedor'] ?? 0);
    $preco_venda      = (float)str_replace(',', '.', $_POST['preco_venda']      ?? 0);
    // str_replace(',', '.') converte vírgula para ponto (padrão do banco)

    if ($produto_id === 0 || $quantidade <= 0) {
        $erro = 'Selecione um produto e informe uma quantidade válida.';
    } else {
        try {
            // Verifica se já existe registro de estoque para este produto
            $stmt = $pdo->prepare("SELECT id FROM estoque WHERE produto_id = ?");
            $stmt->execute([$produto_id]);
            $existe = $stmt->fetch();

            if ($existe) {
                // Se já existe, faz UPDATE somando a quantidade
                $stmt = $pdo->prepare("
                    UPDATE estoque
                    SET quantidade_calca = quantidade_calca + ?,
                        preco_fornecedor = ?,
                        preco_venda = ?
                    WHERE produto_id = ?
                ");
                $stmt->execute([$quantidade, $preco_fornecedor, $preco_venda, $produto_id]);
                $mensagem = 'Estoque atualizado com sucesso!';
            } else {
                // Se não existe, faz INSERT novo
                $stmt = $pdo->prepare("
                    INSERT INTO estoque (produto_id, quantidade_calca, preco_fornecedor, preco_venda)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$produto_id, $quantidade, $preco_fornecedor, $preco_venda]);
                $mensagem = 'Produto adicionado ao estoque!';
            }

        } catch (PDOException $e) {
            $erro = 'Erro: ' . $e->getMessage();
        }
    }
}

// Busca todos os produtos para o select do formulário
$produtos = $pdo->query("SELECT id, nome_produto, marca, tamanho FROM produto ORDER BY nome_produto")->fetchAll();

// Busca o estoque completo com dados do produto (LEFT JOIN inclui produtos sem estoque)
$estoque = $pdo->query("
    SELECT e.*, p.nome_produto, p.marca, p.tamanho, p.cor
    FROM estoque e
    JOIN produto p ON e.produto_id = p.id
    ORDER BY p.nome_produto ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Estoque</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Estoque</h1>
            <p>Gerencie as quantidades e preços dos produtos</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="ri-store-line"></i> Adicionar ao Estoque</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Produto *</label>
                        <select name="produto_id" required>
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo htmlspecialchars("{$p['nome_produto']} – {$p['marca']} – Tam. {$p['tamanho']}"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantidade *</label>
                        <input type="number" name="quantidade" placeholder="Ex: 50" min="1" required>
                    </div>

                    <div class="form-group">
                        <label>Preço de Custo (R$)</label>
                        <input type="text" name="preco_fornecedor" placeholder="Ex: 45,00">
                    </div>

                    <div class="form-group">
                        <label>Preço de Venda (R$)</label>
                        <input type="text" name="preco_venda" placeholder="Ex: 89,90">
                    </div>

                </div>

                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-add-line"></i> Adicionar ao Estoque
                </button>
            </form>
        </div>

        <!-- TABELA DE ESTOQUE -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-list-check"></i> Posição Atual do Estoque</h2>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Marca</th>
                            <th>Tam.</th>
                            <th>Cor</th>
                            <th>Qtd. Disponível</th>
                            <th>Preço Custo</th>
                            <th>Preço Venda</th>
                            <th>Margem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($estoque)): ?>
                            <tr><td colspan="8" style="text-align:center; color:#aaa; padding:30px;">Estoque vazio.</td></tr>
                        <?php else: ?>
                            <?php foreach ($estoque as $e): ?>
                                <?php
                                // Calcula a margem de lucro percentual
                                $margem = ($e['preco_fornecedor'] > 0)
                                    ? (($e['preco_venda'] - $e['preco_fornecedor']) / $e['preco_fornecedor']) * 100
                                    : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($e['nome_produto']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($e['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($e['tamanho']); ?></td>
                                    <td><?php echo htmlspecialchars($e['cor']); ?></td>
                                    <td>
                                        <!-- Exibe em vermelho se quantidade baixa (menor que 5) -->
                                        <span style="color: <?php echo $e['quantidade_calca'] < 5 ? 'var(--perigo)' : 'inherit'; ?>; font-weight: 600;">
                                            <?php echo $e['quantidade_calca']; ?>
                                        </span>
                                    </td>
                                    <td>R$ <?php echo number_format($e['preco_fornecedor'], 2, ',', '.'); ?></td>
                                    <td>R$ <?php echo number_format($e['preco_venda'], 2, ',', '.'); ?></td>
                                    <td style="color: var(--sucesso); font-weight:600;">
                                        <?php echo number_format($margem, 1); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
