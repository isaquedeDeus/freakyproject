<?php
// ============================================================
// ARQUIVO: admin/usuarios.php
// FUNÇÃO: Gerenciamento de usuários do sistema (apenas Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

// -----------------------------------------------
// CADASTRO DE NOVO USUÁRIO
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');
    $senha   = trim($_POST['senha']   ?? '');
    $tipo    = $_POST['tipo'] ?? 'vendedor';

    if (empty($usuario) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres.';
    } else {
        try {
            // password_hash() cria um hash seguro da senha (nunca salve senha pura!)
            // PASSWORD_DEFAULT usa o algoritmo bcrypt, muito seguro
            $hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuario (usuario, senha, tipo) VALUES (?, ?, ?)");
            $stmt->execute([$usuario, $hash, $tipo]);
            $mensagem = 'Usuário criado com sucesso!';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = 'Nome de usuário já existe.';
            } else {
                $erro = 'Erro: ' . $e->getMessage();
            }
        }
    }
}

// -----------------------------------------------
// EXCLUSÃO (não pode excluir a si mesmo)
// -----------------------------------------------
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    // Impede o admin de excluir sua própria conta
    if ($id === $_SESSION['usuario_id']) {
        $erro = 'Você não pode excluir sua própria conta.';
    } else {
        $pdo->prepare("DELETE FROM usuario WHERE id = ?")->execute([$id]);
        $mensagem = 'Usuário excluído.';
    }
}

// Busca todos os usuários
$usuarios = $pdo->query("SELECT id, usuario, tipo, data FROM usuario ORDER BY data DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Usuários</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Usuários do Sistema</h1>
            <p>Gerencie o acesso ao sistema</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO NOVO USUÁRIO -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-user-add-line"></i> Criar Novo Usuário</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group">
                        <label>Nome de Usuário *</label>
                        <input type="text" name="usuario" placeholder="Ex: joao.silva" required>
                    </div>

                    <div class="form-group">
                        <label>Senha * (mín. 6 caracteres)</label>
                        <input type="password" name="senha" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label>Nível de Acesso</label>
                        <select name="tipo">
                            <option value="vendedor">Vendedor (acesso limitado)</option>
                            <option value="admin">Administrador (acesso total)</option>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-save-line"></i> Criar Usuário
                </button>
            </form>
        </div>

        <!-- LISTAGEM -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-shield-user-line"></i> Usuários Cadastrados</h2>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usuário</th>
                            <th>Tipo</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['usuario']); ?></strong>
                                    <!-- Indica o usuário logado atualmente -->
                                    <?php if ($u['id'] === $_SESSION['usuario_id']): ?>
                                        <span style="font-size:0.75rem; color:#888;"> (você)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Badge colorido conforme o tipo -->
                                    <span class="badge badge-<?php echo $u['tipo']; ?>">
                                        <?php echo ucfirst($u['tipo']); ?>
                                        <!-- ucfirst = primeira letra maiúscula -->
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($u['data'])); ?></td>
                                <td>
                                    <?php if ($u['id'] !== $_SESSION['usuario_id']): ?>
                                        <!-- Não exibe botão excluir para o próprio usuário logado -->
                                        <a href="?excluir=<?php echo $u['id']; ?>"
                                           class="btn btn-perigo btn-sm"
                                           onclick="return confirm('Excluir este usuário?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#ccc; font-size:0.8rem;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
