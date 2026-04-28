<?php
// ============================================================
// ARQUIVO: index.php (Login + Cadastro)
// FUNÇÃO: Página de login e cadastro do sistema
// ============================================================

// Inclui autenticação (sessão)
require_once 'includes/auth.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo'] === 'admin') {
        header('Location: admin/painel.php');
    } else {
        header('Location: vendedor/painel.php');
    }
    exit;
}

// Mensagens
$erro = '';
$sucesso = '';

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once 'includes/conexao.php';

    // ========================================================
    // 🔐 LOGIN
    // ========================================================
    if (isset($_POST['acao']) && $_POST['acao'] === 'login') {

        $usuario_input = trim($_POST['usuario'] ?? '');
        $senha_input   = trim($_POST['senha'] ?? '');

        if (empty($usuario_input) || empty($senha_input)) {
            $erro = 'Preencha todos os campos.';
        } else {

            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario = ?");
            $stmt->execute([$usuario_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($senha_input, $user['senha'])) {

                $_SESSION['usuario_id']   = $user['id'];
                $_SESSION['usuario_nome'] = $user['usuario'];
                $_SESSION['tipo']         = $user['tipo'];

                if ($user['tipo'] === 'admin') {
                    header('Location: admin/painel.php');
                } else {
                    header('Location: vendedor/painel.php');
                }
                exit;

            } else {
                $erro = 'Usuário ou senha incorretos.';
            }
        }
    }

    // ========================================================
    // 📝 CADASTRO
    // ========================================================
    if (isset($_POST['acao']) && $_POST['acao'] === 'cadastro') {

        $novo_usuario = trim($_POST['novo_usuario'] ?? '');
        $nova_senha   = trim($_POST['nova_senha'] ?? '');

        if (empty($novo_usuario) || empty($nova_senha)) {
            $erro = 'Preencha todos os campos do cadastro.';
        } else {

            // Verifica se já existe
            $stmt = $pdo->prepare("SELECT id FROM usuario WHERE usuario = ?");
            $stmt->execute([$novo_usuario]);

            if ($stmt->rowCount() > 0) {
                $erro = 'Usuário já existe.';
            } else {

                // Criptografa senha
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // Insere como vendedor (padrão do seu banco)
                $stmt = $pdo->prepare("
                    INSERT INTO usuario (usuario, senha, tipo)
                    VALUES (?, ?, 'vendedor')
                ");

                if ($stmt->execute([$novo_usuario, $senha_hash])) {
                    $sucesso = 'Cadastro realizado com sucesso!';
                } else {
                    $erro = 'Erro ao cadastrar.';
                }
            }
        }
    }
}
?>