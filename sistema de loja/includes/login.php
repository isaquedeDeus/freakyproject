<?php
// Inicia a sessão (necessário para login)
session_start();

// Importa conexão com banco
include "../config/conexao.php";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recebe os dados digitados
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    // Busca o usuário no banco
    $sql = "SELECT * FROM usuario WHERE usuario = :usuario";
    $stmt = $conn->prepare($sql);

    // Proteção contra SQL Injection
    $stmt->bindParam(':usuario', $usuario);

    $stmt->execute();

    // Verifica se encontrou o usuário
    if ($stmt->rowCount() > 0) {

        // Pega os dados do usuário
        $dados = $stmt->fetch();

        // Verifica se a senha está correta
        if (password_verify($senha, $dados['senha'])) {

            // Cria sessão com os dados do usuário
            $_SESSION['usuario'] = $dados['usuario'];
            $_SESSION['tipo'] = $dados['tipo'];

            // Redireciona para o painel
            header("Location: ../painel/painel.php");
            exit();

        } else {
            echo "Senha incorreta!";
        }

    } else {
        echo "Usuário não encontrado!";
    }
}