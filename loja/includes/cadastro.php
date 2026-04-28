<?php
// Importa conexão
include "../config/conexao.php";

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recebe dados do formulário
    $usuario = $_POST['usuario'];

    // Criptografa a senha (segurança)
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    // Verifica se o usuário já existe
    $sql = "SELECT * FROM usuario WHERE usuario = :usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {

        // Se já existir, mostra erro
        echo "Usuário já existe!";

    } else {

        // Insere novo usuário no banco
        $sql = "INSERT INTO usuario (usuario, senha, tipo) 
                VALUES (:usuario, :senha, 'vendedor')";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':senha', $senha);

        // Executa o cadastro
        if ($stmt->execute()) {
            echo "Cadastro realizado com sucesso!";
        } else {
            echo "Erro ao cadastrar!";
        }
    }
}