<?php
// ============================================================
// ARQUIVO: admin/painel.php
// FUNÇÃO: Painel principal do administrador com estatísticas
// ============================================================

// Inclui autenticação (verifica se está logado E se é admin)
require_once '../includes/auth.php';
verificarAdmin(); // Redireciona se não for admin

// Inclui a conexão com o banco de dados
require_once '../includes/conexao.php';

// ---- Busca estatísticas para os cards do painel ----

// Conta total de clientes cadastrados
// fetchColumn() retorna apenas o primeiro valor (o COUNT)
$total_clientes = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();

// Conta total de produtos
$total_produtos = $pdo->query("SELECT COUNT(*) FROM produto")->fetchColumn();

// Conta total de fornecedores
$total_fornecedores = $pdo->query("SELECT COUNT(*) FROM fornecedor")->fetchColumn();

// Soma o valor total de todas as vendas
// COALESCE retorna 0 se não houver vendas (evita null)
$total_vendas = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) FROM venda")->fetchColumn();

// Busca as 5 vendas mais recentes para exibir na tabela
// JOIN une tabelas relacionadas para pegar o nome do cliente e produto
$ultimas_vendas = $pdo->query("
    SELECT v.id, c.nome AS cliente, p.nome_produto AS produto,
           v.quantidade, v.valor_total, v.data_venda
    FROM venda v
    JOIN cliente c ON v.cliente_id = c.id
    JOIN produto p ON v.produto_id = p.id
    ORDER BY v.data_venda DESC  -- Ordena do mais recente ao mais antigo
    LIMIT 5                     -- Apenas as 5 últimas
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Painel</title>
    <!-- Caminho relativo para o CSS (volta uma pasta com ../) -->
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<!-- Layout com sidebar + conteúdo -->
<div class="layout">

    <!-- Inclui a sidebar do admin -->
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <!-- Área de conteúdo principal -->
    <main class="conteudo">

        <!-- Cabeçalho da página -->
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Visão geral do sistema</p>
        </div>

        <!-- Grade de cards de estatísticas -->
        <div class="stats-grid">

            <!-- Card: Total de Clientes -->
            <div class="stat-card">
                <div class="stat-icon azul">
                    <i class="ri-group-line"></i>
                </div>
                <div class="stat-info">
                    <!-- Exibe o valor buscado do banco -->
                    <strong><?php echo $total_clientes; ?></strong>
                    <span>Clientes</span>
                </div>
            </div>

            <!-- Card: Total de Produtos -->
            <div class="stat-card">
                <div class="stat-icon vermelho">
                    <i class="ri-t-shirt-line"></i>
                </div>
                <div class="stat-info">
                    <strong><?php echo $total_produtos; ?></strong>
                    <span>Produtos</span>
                </div>
            </div>

            <!-- Card: Total de Fornecedores -->
            <div class="stat-card">
                <div class="stat-icon laranja">
                    <i class="ri-building-line"></i>
                </div>
                <div class="stat-info">
                    <strong><?php echo $total_fornecedores; ?></strong>
                    <span>Fornecedores</span>
                </div>
            </div>

            <!-- Card: Total em Vendas (formatado como moeda) -->
            <div class="stat-card">
                <div class="stat-icon verde">
                    <i class="ri-money-dollar-circle-line"></i>
                </div>
                <div class="stat-info">
                    <!-- number_format formata o número: 2 casas decimais, vírgula decimal, ponto milhar -->
                    <strong>R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></strong>
                    <span>Total em Vendas</span>
                </div>
            </div>

        </div>

        <!-- Tabela de últimas vendas -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-shopping-cart-line"></i> Últimas Vendas</h2>
                <a href="vendas.php" class="btn btn-secundario btn-sm">Ver todas</a>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Produto</th>
                            <th>Qtd</th>
                            <th>Total</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimas_vendas)): ?>
                            <!-- Exibe mensagem quando não há vendas -->
                            <tr>
                                <td colspan="6" style="text-align:center; color:#aaa; padding:30px;">
                                    Nenhuma venda registrada ainda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <!-- Loop para exibir cada venda -->
                            <?php foreach ($ultimas_vendas as $venda): ?>
                                <tr>
                                    <td><strong>#<?php echo $venda['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($venda['cliente']); ?></td>
                                    <td><?php echo htmlspecialchars($venda['produto']); ?></td>
                                    <td><?php echo $venda['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></td>
                                    <!-- date() formata a data do banco para o padrão brasileiro -->
                                    <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
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
