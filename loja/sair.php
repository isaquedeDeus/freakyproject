<?php
// ============================================================
// ARQUIVO: sair.php
// FUNÇÃO: Encerrar a sessão do usuário (logout)
// ============================================================

// Inicia a sessão para ter acesso aos dados dela
session_start();

// Destrói todos os dados da sessão (apaga usuário logado, tipo, etc.)
session_destroy();

// Redireciona para a página de login
header('Location: index.php');
exit;
?>
