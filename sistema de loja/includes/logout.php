<?php
// Inicia sessão
session_start();

// Destroi TODOS os dados da sessão (logout)
session_destroy();

// Redireciona para tela de login
header("Location: ../index.php");
exit();