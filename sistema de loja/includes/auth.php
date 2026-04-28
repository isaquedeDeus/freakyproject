<?php
// ============================================================
// ARQUIVO: includes/auth.php
// FUNÇÃO: Funções de autenticação e controle de sessão
// ============================================================

// Inicia a sessão PHP (necessário para usar $_SESSION)
// Sessões guardam dados do usuário entre páginas diferentes
session_start();

// -----------------------------------------------
// FUNÇÃO: verificarLogin()
// Verifica se o usuário está logado
// Se não estiver, redireciona para o login
// -----------------------------------------------
function verificarLogin() {
    // Verifica se a chave 'usuario_id' existe na sessão
    if (!isset($_SESSION['usuario_id'])) {
        // Redireciona para a página de login
        header('Location: /loja/index.php');
        exit; // Para a execução do script após o redirecionamento
    }
}

// -----------------------------------------------
// FUNÇÃO: verificarAdmin()
// Verifica se o usuário logado é um administrador
// Se não for, redireciona para o painel do vendedor
// -----------------------------------------------
function verificarAdmin() {
    verificarLogin(); // Primeiro verifica se está logado
    if ($_SESSION['tipo'] !== 'admin') {
        // Se não for admin, manda para o painel do vendedor
        header('Location: /loja/vendedor/painel.php');
        exit;
    }
}

// -----------------------------------------------
// FUNÇÃO: verificarVendedor()
// Verifica se o usuário é vendedor ou admin
// Admins também podem acessar páginas de vendedor
// -----------------------------------------------
function verificarVendedor() {
    verificarLogin(); // Verifica se está logado
    // Verifica se é vendedor OU admin (ambos podem acessar)
    if ($_SESSION['tipo'] !== 'vendedor' && $_SESSION['tipo'] !== 'admin') {
        header('Location: /loja/index.php');
        exit;
    }
}

// -----------------------------------------------
// FUNÇÃO: getTipo()
// Retorna o tipo do usuário logado (admin ou vendedor)
// -----------------------------------------------
function getTipo() {
    return $_SESSION['tipo'] ?? ''; // ?? '' retorna vazio se não existir
}

// -----------------------------------------------
// FUNÇÃO: getNome()
// Retorna o nome do usuário logado
// -----------------------------------------------
function getNome() {
    return $_SESSION['usuario_nome'] ?? '';
}
?>
