<?php
// ============================================================
// ARQUIVO: admin/produtos.php
// FUNÇÃO: Cadastro e listagem de produtos (Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

// -----------------------------------------------
// PROCESSAMENTO POST
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome_produto  = trim($_POST['nome_produto']  ?? '');
    $marca         = trim($_POST['marca']         ?? '');
    $cor           = trim($_POST['cor']           ?? '');
    $tamanho       = trim($_POST['tamanho']       ?? '');
    $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);

    if (empty($nome_produto) || empty($marca) || $fornecedor_id === 0) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO produto (nome_produto, marca, cor, tamanho, fornecedor_id)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nome_produto, $marca, $cor, $tamanho, $fornecedor_id]);
            $mensagem = 'Produto cadastrado com sucesso!';
        } catch (PDOException $e) {
            $erro = 'Erro ao cadastrar produto: ' . $e->getMessage();
        }
    }
}

// -----------------------------------------------
// EXCLUSÃO
// -----------------------------------------------
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    try {
        $pdo->prepare("DELETE FROM produto WHERE id = ?")->execute([$id]);
        $mensagem = 'Produto excluído com sucesso!';
    } catch (PDOException $e) {
        $erro = 'Não é possível excluir: produto possui vendas ou estoque vinculado.';
    }
}

// Busca todos os fornecedores para o SELECT do formulário
$fornecedores = $pdo->query("SELECT id, nome FROM fornecedor ORDER BY nome")->fetchAll();

// Busca todos os produtos com o nome do fornecedor (JOIN)
$produtos = $pdo->query("
    SELECT p.*, f.nome AS fornecedor_nome
    FROM produto p
    JOIN fornecedor f ON p.fornecedor_id = f.id
    ORDER BY p.nome_produto ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Produtos</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Produtos</h1>
            <p>Gerencie o catálogo de produtos</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-t-shirt-line"></i> Novo Produto</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Nome do Produto *</label>
                        <input type="text" name="nome_produto" placeholder="Ex: Calça Jeans Slim" required>
                    </div>

                    <div class="form-group">
                        <label>Marca *</label>
                        <input type="text" name="marca" placeholder="Ex: Levi's" required>
                    </div>

                    <div class="form-group">
                        <label>Cor</label>
                        <input type="text" name="cor" placeholder="Ex: Azul escuro">
                    </div>

                    <div class="form-group">
                        <label>Tamanho</label>
                        <!-- Select com tamanhos comuns de roupas -->
                        <select name="tamanho">
                            <option value="">Selecione...</option>
                            <option value="PP">PP</option>
                            <option value="P">P</option>
                            <option value="M">M</option>
                            <option value="G">G</option>
                            <option value="GG">GG</option>
                            <option value="XGG">XGG</option>
                            <?php
                            // Tamanhos numéricos de 34 a 54
                            for ($i = 34; $i <= 54; $i += 2):
                            ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fornecedor *</label>
                        <select name="fornecedor_id" required>
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?php echo $f['id']; ?>">
                                    <?php echo htmlspecialchars($f['nome']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-save-line"></i> Cadastrar Produto
                </button>
            </form>
        </div>

        <!-- LISTAGEM -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-t-shirt-line"></i> Produtos Cadastrados</h2>
                <span style="color:#888; font-size:0.9rem;"><?php echo count($produtos); ?> produto(s)</span>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Marca</th>
                            <th>Cor</th>
                            <th>Tamanho</th>
                            <th>Fornecedor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produtos)): ?>
                            <tr><td colspan="7" style="text-align:center; color:#aaa; padding:30px;">Nenhum produto cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($p['nome_produto']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($p['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($p['cor']); ?></td>
                                    <td><?php echo htmlspecialchars($p['tamanho']); ?></td>
                                    <td><?php echo htmlspecialchars($p['fornecedor_nome']); ?></td>
                                    <td>
                                        <a href="?excluir=<?php echo $p['id']; ?>"
                                           class="btn btn-perigo btn-sm"
                                           onclick="return confirm('Excluir este produto?')">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</body>
</html>
