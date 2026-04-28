<?php
// ============================================================
// ARQUIVO: vendedor/painel.php
// FUNÇÃO: Painel do vendedor com suas vendas do dia
// ============================================================

require_once '../includes/auth.php';
verificarVendedor(); // Vendedor E admin podem acessar
require_once '../includes/conexao.php';

// ---- Estatísticas gerais (visível para vendedor) ----
$total_clientes = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
$total_produtos  = $pdo->query("
    SELECT COUNT(*) FROM estoque WHERE quantidade_calca > 0
")->fetchColumn(); // Só produtos com estoque disponível

// Total de vendas de HOJE
$hoje = date('Y-m-d'); // Data atual no formato do banco
$stmt = $pdo->prepare("
    SELECT COUNT(*) as qtd, COALESCE(SUM(valor_total), 0) as total
    FROM venda
    WHERE DATE(data_venda) = ?
");
$stmt->execute([$hoje]);
$vendas_hoje = $stmt->fetch();

// Últimas 5 vendas (sem filtro de vendedor para contexto geral)
$ultimas = $pdo->query("
    SELECT v.id, c.nome AS cliente, p.nome_produto AS produto,
           v.quantidade, v.valor_total, v.data_venda
    FROM venda v
    JOIN cliente c ON v.cliente_id = c.id
    JOIN produto p ON v.produto_id = p.id
    ORDER BY v.data_venda DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor – Painel</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_vendedor.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Meu Painel</h1>
            <p>Bem-vindo, <?php echo htmlspecialchars(getNome()); ?>! Hoje é <?php echo date('d/m/Y'); ?>.</p>
        </div>

        <!-- Cards de estatísticas do vendedor -->
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon azul"><i class="ri-group-line"></i></div>
                <div class="stat-info">
                    <strong><?php echo $total_clientes; ?></strong>
                    <span>Clientes Cadastrados</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon verde"><i class="ri-store-line"></i></div>
                <div class="stat-info">
                    <strong><?php echo $total_produtos; ?></strong>
                    <span>Produtos Disponíveis</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon vermelho"><i class="ri-shopping-cart-line"></i></div>
                <div class="stat-info">
                    <strong><?php echo $vendas_hoje['qtd']; ?></strong>
                    <span>Vendas Hoje</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon laranja"><i class="ri-money-dollar-circle-line"></i></div>
                <div class="stat-info">
                    <strong>R$ <?php echo number_format($vendas_hoje['total'], 2, ',', '.'); ?></strong>
                    <span>Faturamento Hoje</span>
                </div>
            </div>

        </div>

        <!-- Atalhos rápidos -->
        <div class="card">
            <div class="card-header">
                <h2>Ações Rápidas</h2>
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <a href="clientes.php" class="btn btn-secundario">
                    <i class="ri-user-add-line"></i> Cadastrar Cliente
                </a>
                <a href="vendas.php" class="btn btn-primario">
                    <i class="ri-shopping-cart-line"></i> Nova Venda
                </a>
                <a href="minhas_vendas.php" class="btn" style="background:#f4f6fb; color:var(--primaria);">
                    <i class="ri-list-check"></i> Ver Minhas Vendas
                </a>
            </div>
        </div>

        <!-- Últimas vendas -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-shopping-cart-line"></i> Últimas Vendas</h2>
            </div>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr><th>#</th><th>Cliente</th><th>Produto</th><th>Qtd</th><th>Total</th><th>Data</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimas)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px;">Nenhuma venda ainda.</td></tr>
                        <?php else: ?>
                            <?php foreach ($ultimas as $v): ?>
                                <tr>
                                    <td>#<?php echo $v['id']; ?></td>
                                    <td><?php echo htmlspecialchars($v['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($v['produto']); ?></td>
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

</body>
</html>
