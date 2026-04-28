<?php
// ============================================================
// ARQUIVO: vendedor/minhas_vendas.php
// FUNÇÃO: Histórico de todas as vendas (visão do vendedor)
// ============================================================

require_once '../includes/auth.php';
verificarVendedor(); // Apenas vendedor e admin acessam
require_once '../includes/conexao.php';

// Busca todas as vendas com dados completos de cliente, produto e vendedor
// O vendedor vê todas as vendas do sistema (não só as dele)
// para ter contexto do que foi vendido
$vendas = $pdo->query("
    SELECT v.id, c.nome AS cliente, p.nome_produto AS produto,
           vd.nome AS vendedor, v.quantidade, v.valor_total, v.data_venda
    FROM venda v
    JOIN cliente  c  ON v.cliente_id  = c.id
    JOIN produto  p  ON v.produto_id  = p.id
    JOIN vendedor vd ON v.vendedor_id = vd.id
    ORDER BY v.data_venda DESC
")->fetchAll();

// Calcula o total geral de todas as vendas
$total_geral = array_sum(array_column($vendas, 'valor_total'));
// array_column() extrai todos os valores da coluna 'valor_total'
// array_sum() soma todos esses valores
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor – Histórico de Vendas</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_vendedor.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Histórico de Vendas</h1>
            <p>Todas as vendas registradas no sistema</p>
        </div>

        <!-- Resumo rápido no topo -->
        <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); max-width: 500px;">
            <div class="stat-card">
                <div class="stat-icon vermelho"><i class="ri-shopping-cart-line"></i></div>
                <div class="stat-info">
                    <strong><?php echo count($vendas); ?></strong>
                    <span>Total de Vendas</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon verde"><i class="ri-money-dollar-circle-line"></i></div>
                <div class="stat-info">
                    <strong>R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></strong>
                    <span>Faturamento Total</span>
                </div>
            </div>
        </div>

        <!-- Tabela de vendas -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-list-check"></i> Todas as Vendas</h2>
                <span style="color:#888; font-size:0.9rem;"><?php echo count($vendas); ?> registro(s)</span>
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
                            <tr>
                                <td colspan="7" style="text-align:center; color:#aaa; padding:30px;">
                                    Nenhuma venda registrada ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendas as $v): ?>
                                <tr>
                                    <td><strong>#<?php echo $v['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($v['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($v['produto']); ?></td>
                                    <td><?php echo htmlspecialchars($v['vendedor']); ?></td>
                                    <td><?php echo $v['quantidade']; ?></td>
                                    <td><strong>R$ <?php echo number_format($v['valor_total'], 2, ',', '.'); ?></strong></td>
                                    <!-- strtotime() converte string de data para timestamp Unix -->
                                    <!-- date() formata o timestamp no padrão brasileiro -->
                                    <td><?php echo date('d/m/Y H:i', strtotime($v['data_venda'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Rodapé da tabela com total -->
            <?php if (!empty($vendas)): ?>
                <div style="text-align:right; padding:16px; border-top:1px solid var(--borda); font-weight:700; color:var(--primaria);">
                    Total Geral: R$ <?php echo number_format($total_geral, 2, ',', '.'); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

</body>
</html>
