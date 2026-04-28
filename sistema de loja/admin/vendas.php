<?php
// ============================================================
// ARQUIVO: admin/vendas.php
// FUNÇÃO: Registro de vendas e relatório completo (Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

// -----------------------------------------------
// PROCESSAMENTO POST: Registrar nova venda
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cliente_id  = (int)($_POST['cliente_id']  ?? 0);
    $produto_id  = (int)($_POST['produto_id']  ?? 0);
    $vendedor_id = (int)($_POST['vendedor_id'] ?? 0);
    $quantidade  = (int)($_POST['quantidade']  ?? 0);

    if (!$cliente_id || !$produto_id || !$vendedor_id || $quantidade <= 0) {
        $erro = 'Preencha todos os campos corretamente.';
    } else {
        // Busca o preço de venda e quantidade em estoque do produto
        $stmt = $pdo->prepare("SELECT preco_venda, quantidade_calca FROM estoque WHERE produto_id = ?");
        $stmt->execute([$produto_id]);
        $estoque = $stmt->fetch();

        if (!$estoque) {
            $erro = 'Produto sem estoque cadastrado.';
        } elseif ($estoque['quantidade_calca'] < $quantidade) {
            // Verifica se tem estoque suficiente
            $erro = "Estoque insuficiente. Disponível: {$estoque['quantidade_calca']} unidades.";
        } else {
            try {
                // Calcula o valor total da venda
                $valor_total = $estoque['preco_venda'] * $quantidade;

                // Inicia uma transação: ou tudo funciona ou nada é salvo
                $pdo->beginTransaction();

                // 1. Insere a venda
                $stmt = $pdo->prepare("
                    INSERT INTO venda (cliente_id, produto_id, vendedor_id, quantidade, valor_total)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$cliente_id, $produto_id, $vendedor_id, $quantidade, $valor_total]);

                // 2. Reduz a quantidade no estoque
                $stmt = $pdo->prepare("
                    UPDATE estoque
                    SET quantidade_calca = quantidade_calca - ?
                    WHERE produto_id = ?
                ");
                $stmt->execute([$quantidade, $produto_id]);

                // 3. Atualiza o contador de vendas do vendedor
                $pdo->prepare("
                    UPDATE vendedor SET quantidade_vendas = quantidade_vendas + 1 WHERE id = ?
                ")->execute([$vendedor_id]);

                // Confirma a transação (salva tudo de uma vez)
                $pdo->commit();

                $mensagem = "Venda registrada! Total: R$ " . number_format($valor_total, 2, ',', '.');

            } catch (PDOException $e) {
                $pdo->rollBack(); // Cancela tudo se houver erro
                $erro = 'Erro ao registrar venda: ' . $e->getMessage();
            }
        }
    }
}

// Dados para os selects do formulário
$clientes  = $pdo->query("SELECT id, nome FROM cliente ORDER BY nome")->fetchAll();
$vendedores = $pdo->query("SELECT id, nome FROM vendedor ORDER BY nome")->fetchAll();
// Somente produtos com estoque disponível aparecem no select
$produtos  = $pdo->query("
    SELECT p.id, p.nome_produto, p.marca, p.tamanho, e.preco_venda, e.quantidade_calca
    FROM produto p
    JOIN estoque e ON p.id = e.produto_id
    WHERE e.quantidade_calca > 0
    ORDER BY p.nome_produto
")->fetchAll();

// Listagem de todas as vendas
$vendas = $pdo->query("
    SELECT v.id, c.nome AS cliente, p.nome_produto AS produto,
           vd.nome AS vendedor, v.quantidade, v.valor_total, v.data_venda
    FROM venda v
    JOIN cliente c   ON v.cliente_id  = c.id
    JOIN produto p   ON v.produto_id  = p.id
    JOIN vendedor vd ON v.vendedor_id = vd.id
    ORDER BY v.data_venda DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Vendas</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Vendas</h1>
            <p>Registre e acompanhe as vendas da loja</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="ri-shopping-cart-line"></i> Nova Venda</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group">
                        <label>Cliente *</label>
                        <select name="cliente_id" required>
                            <option value="">Selecione o cliente...</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Produto *</label>
                        <select name="produto_id" id="select_produto" onchange="atualizarPreco(this)" required>
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <!-- data-preco armazena o preço no HTML para JavaScript ler -->
                                <option value="<?php echo $p['id']; ?>"
                                        data-preco="<?php echo $p['preco_venda']; ?>"
                                        data-estoque="<?php echo $p['quantidade_calca']; ?>">
                                    <?php echo htmlspecialchars("{$p['nome_produto']} – {$p['marca']} – Tam.{$p['tamanho']} – R$ " . number_format($p['preco_venda'], 2, ',', '.')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Vendedor *</label>
                        <select name="vendedor_id" required>
                            <option value="">Selecione o vendedor...</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantidade *</label>
                        <input type="number" name="quantidade" id="qtd" placeholder="Ex: 2" min="1" onchange="calcularTotal()" required>
                    </div>

                </div>

                <!-- Exibição do preço unitário e total (calculado por JS) -->
                <div style="background:#f4f6fb; border-radius:10px; padding:16px; margin:12px 0; display:flex; gap:24px;">
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Preço Unitário</span>
                        <strong id="preco_unit" style="display:block; font-size:1.1rem; color:var(--primaria)">—</strong>
                    </div>
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Total da Venda</span>
                        <strong id="total_venda" style="display:block; font-size:1.1rem; color:var(--sucesso)">—</strong>
                    </div>
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Em Estoque</span>
                        <strong id="qtd_estoque" style="display:block; font-size:1.1rem; color:var(--destaque2)">—</strong>
                    </div>
                </div>

                <button type="submit" class="btn btn-primario" style="width:auto;">
                    <i class="ri-check-line"></i> Registrar Venda
                </button>
            </form>
        </div>

        <!-- HISTÓRICO DE VENDAS -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-list-check"></i> Histórico de Vendas</h2>
                <span style="color:#888; font-size:0.9rem;"><?php echo count($vendas); ?> venda(s)</span>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Vendedor</th>
                            <th>Qtd</th>
                            <th>Total</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vendas)): ?>
                            <tr><td colspan="7" style="text-align:center;color:#aaa;padding:30px;">Nenhuma venda registrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($vendas as $v): ?>
                                <tr>
                                    <td>#<?php echo $v['id']; ?></td>
                                    <td><?php echo htmlspecialchars($v['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($v['produto']); ?></td>
                                    <td><?php echo htmlspecialchars($v['vendedor']); ?></td>
                                    <td><?php echo $v['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($v['valor_total'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($v['data_venda'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script>
// Quando o usuário seleciona um produto, mostra o preço e estoque
function atualizarPreco(select) {
    const option = select.options[select.selectedIndex]; // Opção selecionada
    const preco  = parseFloat(option.dataset.preco) || 0; // Lê data-preco
    const qtdEst = option.dataset.estoque || '—';         // Lê data-estoque

    // Formata e exibe o preço em formato brasileiro
    document.getElementById('preco_unit').textContent  = preco ? 'R$ ' + preco.toFixed(2).replace('.', ',') : '—';
    document.getElementById('qtd_estoque').textContent = qtdEst + ' unidades';
    document.getElementById('total_venda').textContent = '—';
}

// Calcula o total quando o usuário informa a quantidade
function calcularTotal() {
    const select = document.getElementById('select_produto');
    const option = select.options[select.selectedIndex];
    const preco  = parseFloat(option.dataset.preco) || 0;
    const qtd    = parseInt(document.getElementById('qtd').value) || 0;
    const total  = preco * qtd;
    document.getElementById('total_venda').textContent = total ? 'R$ ' + total.toFixed(2).replace('.', ',') : '—';
}
</script>

</body>
</html>
