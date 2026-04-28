<?php
// Inicia sessão
session_start();

// Proteção: se não estiver logado, volta pro login
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Painel</title>
</head>
<body>

<!-- Mostra nome do usuário logado -->
<h1>Bem-vindo, <?php echo $_SESSION['usuario']; ?></h1>

<?php
// Verifica o tipo de usuário
if ($_SESSION['tipo'] == 'admin') {

    // Conteúdo exclusivo do ADMIN
    echo "<h2>Área do ADMIN</h2>";

} else {

    // Conteúdo do VENDEDOR
    echo "<h2>Área do VENDEDOR</h2>";
}
?>

<br>

<!-- Botão de logout -->
<a href="../logout.php">
    <button>Sair</button>
</a>

</body>
</html>