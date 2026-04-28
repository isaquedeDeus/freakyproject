<?php
// Dados de conexão com o banco
$host = "localhost";        // servidor do banco (normalmente localhost)
$db = "loja_system";        // nome do banco de dados
$user = "root";             // usuário do banco
$pass = "";                 // senha do banco (vazio no XAMPP)

// Tentativa de conexão usando PDO
try {
    // Cria a conexão com o banco
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);

    // Configura o PDO para mostrar erros
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Caso dê erro, exibe a mensagem
    echo "Erro na conexão: " . $e->getMessage();
}