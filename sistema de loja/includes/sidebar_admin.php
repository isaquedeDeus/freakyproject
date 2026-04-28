<?php
// ============================================================
// ARQUIVO: includes/sidebar_admin.php
// FUNÇÃO: Menu lateral do administrador (incluído em todas as páginas admin)
// ============================================================

// Pega a URL atual para marcar o link ativo no menu
$pagina_atual = basename($_SERVER['PHP_SELF']); // Ex: "painel.php"
?>

<!-- Sidebar do Admin -->
<aside class="sidebar">

    <!-- Logo / Nome do sistema -->
    <div class="sidebar-logo">
        <h2>Loja<span>System</span></h2>
        <!-- getNome() retorna o nome do usuário da sessão -->
        <small>Bem-vindo, <?php echo htmlspecialchars(getNome()); ?></small>
    </div>

    <!-- Menu de navegação -->
    <nav class="sidebar-nav">

        <!-- SEÇÃO: PAINEL -->
        <span class="sidebar-section">Painel</span>

        <!-- Link ativo quando estiver em painel.php -->
        <!-- A classe "ativo" é adicionada condicionalmente com PHP (ternário) -->
        <a href="painel.php" class="<?php echo $pagina_atual === 'painel.php' ? 'ativo' : ''; ?>">
            <i class="ri-dashboard-line"></i> Dashboard
        </a>

        <!-- SEÇÃO: CADASTROS -->
        <span class="sidebar-section">Cadastros</span>

        <a href="clientes.php" class="<?php echo $pagina_atual === 'clientes.php' ? 'ativo' : ''; ?>">
            <i class="ri-group-line"></i> Clientes
        </a>

        <a href="fornecedores.php" class="<?php echo $pagina_atual === 'fornecedores.php' ? 'ativo' : ''; ?>">
            <i class="ri-building-line"></i> Fornecedores
        </a>

        <a href="produtos.php" class="<?php echo $pagina_atual === 'produtos.php' ? 'ativo' : ''; ?>">
            <i class="ri-t-shirt-line"></i> Produtos
        </a>

        <a href="vendedores.php" class="<?php echo $pagina_atual === 'vendedores.php' ? 'ativo' : ''; ?>">
            <i class="ri-user-star-line"></i> Vendedores
        </a>

        <!-- SEÇÃO: OPERAÇÕES -->
        <span class="sidebar-section">Operações</span>

        <a href="estoque.php" class="<?php echo $pagina_atual === 'estoque.php' ? 'ativo' : ''; ?>">
            <i class="ri-store-line"></i> Estoque
        </a>

        <a href="vendas.php" class="<?php echo $pagina_atual === 'vendas.php' ? 'ativo' : ''; ?>">
            <i class="ri-shopping-cart-line"></i> Vendas
        </a>

        <!-- SEÇÃO: SISTEMA (só para admin) -->
        <span class="sidebar-section">Sistema</span>

        <a href="usuarios.php" class="<?php echo $pagina_atual === 'usuarios.php' ? 'ativo' : ''; ?>">
            <i class="ri-shield-user-line"></i> Usuários
        </a>

    </nav>

    <!-- Rodapé da sidebar: info do usuário + botão sair -->
    <div class="sidebar-footer">
        <div class="user-info">
            <!-- Avatar com a primeira letra do nome em maiúscula -->
            <div class="user-avatar">
                <?php echo strtoupper(substr(getNome(), 0, 1)); ?>
                <!-- strtoupper = maiúscula, substr = pega os primeiros caracteres -->
            </div>
            <div class="user-info-text">
                <strong><?php echo htmlspecialchars(getNome()); ?></strong>
                <span><?php echo getTipo(); ?></span>
            </div>
        </div>
        <!-- Link de sair aponta para o script de logout -->
        <a href="../sair.php" class="sair">
            <i class="ri-logout-box-line"></i> Sair do sistema
        </a>
    </div>

</aside>
