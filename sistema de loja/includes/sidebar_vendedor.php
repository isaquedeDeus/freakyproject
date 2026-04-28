<?php
// ============================================================
// ARQUIVO: includes/sidebar_vendedor.php
// FUNÇÃO: Menu lateral do vendedor (acesso restrito)
// ============================================================

$pagina_atual = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">

    <div class="sidebar-logo">
        <h2>Loja<span>System</span></h2>
        <small>Bem-vindo, <?php echo htmlspecialchars(getNome()); ?></small>
    </div>

    <nav class="sidebar-nav">

        <!-- O vendedor vê apenas o painel, clientes, e vendas -->
        <span class="sidebar-section">Painel</span>

        <a href="painel.php" class="<?php echo $pagina_atual === 'painel.php' ? 'ativo' : ''; ?>">
            <i class="ri-dashboard-line"></i> Meu Painel
        </a>

        <!-- SEÇÃO: CADASTROS - vendedor só pode cadastrar clientes -->
        <span class="sidebar-section">Cadastros</span>

        <a href="clientes.php" class="<?php echo $pagina_atual === 'clientes.php' ? 'ativo' : ''; ?>">
            <i class="ri-group-line"></i> Clientes
        </a>

        <!-- SEÇÃO: OPERAÇÕES -->
        <span class="sidebar-section">Operações</span>

        <a href="vendas.php" class="<?php echo $pagina_atual === 'vendas.php' ? 'ativo' : ''; ?>">
            <i class="ri-shopping-cart-line"></i> Registrar Venda
        </a>

        <a href="minhas_vendas.php" class="<?php echo $pagina_atual === 'minhas_vendas.php' ? 'ativo' : ''; ?>">
            <i class="ri-list-check"></i> Minhas Vendas
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr(getNome(), 0, 1)); ?>
            </div>
            <div class="user-info-text">
                <strong><?php echo htmlspecialchars(getNome()); ?></strong>
                <span><?php echo getTipo(); ?></span>
            </div>
        </div>
        <a href="../sair.php" class="sair">
            <i class="ri-logout-box-line"></i> Sair do sistema
        </a>
    </div>

</aside>
