<?php
// ============================================================
// ARQUIVO: vendedor/vendas.php
// FUNÇÃO: Registrar venda (acesso do vendedor)
// ============================================================

// Inclui arquivos de autenticação e conexão com banco de dados
require_once '../includes/auth.php';
verificarVendedor(); // Função que bloqueia acesso se não for vendedor
require_once '../includes/conexao.php';

$mensagem = ''; // Variável para mensagens de sucesso
$erro = '';     // Variável para mensagens de erro

// --- PROCESSAMENTO DO FORMULÁRIO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e conversão dos dados recebidos
    $cliente_id = (int)($_POST['cliente_id'] ?? 0);
    $produto_id = (int)($_POST['produto_id'] ?? 0);
    $vendedor_id = (int)($_POST['vendedor_id'] ?? 0);
    $quantidade = (int)($_POST['quantidade'] ?? 0);

    // Validação básica
    if (!$cliente_id || !$produto_id || !$vendedor_id || $quantidade <= 0) {
        $erro = 'Preencha todos os campos.';
    } else {
        // Busca o produto para verificar preço e estoque atual
        $stmt = $pdo->prepare("SELECT preco_venda, quantidade_calca FROM estoque WHERE produto_id = ?");
        $stmt->execute([$produto_id]);
        $estoque = $stmt->fetch();

        if (!$estoque) {
            $erro = 'Produto sem estoque.';
        } elseif ($estoque['quantidade_calca'] < $quantidade) {
            // Verifica se a quantidade vendida é maior que a disponível
            $erro = "Estoque insuficiente. Disponível: {$estoque['quantidade_calca']} un.";
        } else {
            // --- INÍCIO DA TRANSAÇÃO SQL (Segurança dos dados) ---
            try {
                $valor_total = $estoque['preco_venda'] * $quantidade;
                $pdo->beginTransaction(); // Inicia transação

                // 1. Registra a venda
                $stmt = $pdo->prepare("INSERT INTO venda (cliente_id, produto_id, vendedor_id, quantidade, valor_total) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$cliente_id, $produto_id, $vendedor_id, $quantidade, $valor_total]);

                // 2. Atualiza o estoque (baixa na quantidade)
                $pdo->prepare("UPDATE estoque SET quantidade_calca = quantidade_calca - ? WHERE produto_id = ?")->execute([$quantidade, $produto_id]);

                // 3. Atualiza o contador de vendas do vendedor
                $pdo->prepare("UPDATE vendedor SET quantidade_vendas = quantidade_vendas + 1 WHERE id = ?")->execute([$vendedor_id]);

                $pdo->commit(); // Finaliza e salva todas as ações
                $mensagem = "Venda registrada! Total: R$ " . number_format($valor_total, 2, ',', '.');
            } catch (PDOException $e) {
                // Se der erro, desfaz tudo (rollback)
                $pdo->rollBack();
                $erro = 'Erro: ' . $e->getMessage();
            }
        }
    }
}

// --- BUSCA DE DADOS PARA POPULAR O FORMULÁRIO ---
// Lista de clientes
$clientes = $pdo->query("SELECT id, nome FROM cliente ORDER BY nome")->fetchAll();
// Lista de vendedores
$vendedores = $pdo->query("SELECT id, nome FROM vendedor ORDER BY nome")->fetchAll();
// Lista de produtos apenas com estoque > 0
$produtos = $pdo->query("
    SELECT p.id, p.nome_produto, p.marca, p.tamanho, e.preco_venda, e.quantidade_calca
    FROM produto p
    JOIN estoque e ON p.id = e.produto_id
    WHERE e.quantidade_calca > 0
    ORDER BY p.nome_produto
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor – Nova Venda</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
<div class="layout">
    <?php require_once '../includes/sidebar_vendedor.php'; // Sidebar de navegação ?>

    <main class="conteudo">
        <div class="page-header">
            <h1>Registrar Venda</h1>
            <p>Preencha os dados abaixo para registrar uma venda</p>
        </div>

        <!-- Exibe mensagens de feedback -->
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
                    <!-- Seleção de Cliente -->
                    <div class="form-group">
                        <label>Cliente *</label>
                        <select name="cliente_id" required>
                            <option value="">Selecione o cliente...</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Seleção de Produto com dados via data-attributes para JS -->
                    <div class="form-group">
                        <label>Produto *</label>
                        <select name="produto_id" id="select_produto" onchange="atualizarPreco(this)" required>
                            <option value="">Selecione o produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?php echo $p['id']; ?>" data-preco="<?php echo $p['preco_venda']; ?>" data-estoque="<?php echo $p['quantidade_calca']; ?>">
                                    <?php echo htmlspecialchars("{$p['nome_produto']} – {$p['marca']} – Tam.{$p['tamanho']} – R$ " . number_format($p['preco_venda'], 2, ',', '.')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Seleção de Vendedor -->
                    <div class="form-group">
                        <label>Vendedor *</label>
                        <select name="vendedor_id" required>
                            <option value="">Selecione o vendedor...</option>
                            <?php foreach ($vendedores as $v): ?>
                                <option value="<?php echo $v['id']; ?>"><?php echo htmlspecialchars($v['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Quantidade -->
                    <div class="form-group">
                        <label>Quantidade *</label>
                        <input type="number" name="quantidade" id="qtd" min="1" placeholder="Ex: 1" onchange="calcularTotal()" required>
                    </div>
                </div>

                <!-- Resumo da Venda (JS) -->
                <div style="background:#f4f6fb; border-radius:10px; padding:16px; margin:12px 0; display:flex; gap:24px;">
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Preço Unitário</span>
                        <strong id="preco_unit" style="display:block; font-size:1.1rem;">—</strong>
                    </div>
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Total</span>
                        <strong id="total_venda" style="display:block; font-size:1.1rem; color:var(--sucesso);">—</strong>
                    </div>
                    <div>
                        <span style="font-size:0.8rem; color:#888;">Em Estoque</span>
                        <strong id="qtd_estoque" style="display:block; font-size:1.1rem;">—</strong>
                    </div>
                </div>

                <button type="submit" class="btn btn-primario" style="width:auto;">
                    <i class="ri-check-line"></i> Finalizar Venda
                </button>
            </form>
        </div>
    </main>
</div>

<script>
    // Função JS: Atualiza preço e estoque na tela ao selecionar o produto
    function atualizarPreco(select) {
        const opt = select.options[select.selectedIndex];
        const preco = parseFloat(opt.dataset.preco) || 0;
        document.getElementById('preco_unit').textContent = preco ? 'R$ ' + preco.toFixed(2).replace('.', ',') : '—';
        document.getElementById('qtd_estoque').textContent = (opt.dataset.estoque || '—') + ' un.';
        document.getElementById('total_venda').textContent = '—'; // Reseta o total
    }

    // Função JS: Calcula e exibe o total ao mudar a quantidade
    function calcularTotal() {
        const opt = document.getElementById('select_produto').options[document.getElementById('select_produto').selectedIndex];
        const preco = parseFloat(opt.dataset.preco) || 0;
        const qtd = parseInt(document.getElementById('qtd').value) || 0;
        const tot = preco * qtd;
        document.getElementById('total_venda').textContent = tot ? 'R$ ' + tot.toFixed(2).replace('.', ',') : '—';
    }
</script>
</body>
</html>
