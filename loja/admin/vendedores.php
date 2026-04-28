<?php
// ============================================================
// ARQUIVO: admin/vendedores.php
// FUNÇÃO: Cadastro e gestão de vendedores (Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome']     ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($nome)) {
        $erro = 'O nome do vendedor é obrigatório.';
    } else {
        try {
            // Insere o vendedor com quantidade de vendas = 0
            // cliente_vendas começa como array vazio em JSON
            $stmt = $pdo->prepare("
                INSERT INTO vendedor (nome, telefone, quantidade_vendas, cliente_vendas)
                VALUES (?, ?, 0, '')
            ");
            $stmt->execute([$nome, $telefone]);
            $mensagem = 'Vendedor cadastrado com sucesso!';
        } catch (PDOException $e) {
            $erro = 'Erro: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    try {
        $pdo->prepare("DELETE FROM vendedor WHERE id = ?")->execute([$id]);
        $mensagem = 'Vendedor excluído!';
    } catch (PDOException $e) {
        $erro = 'Não é possível excluir: vendedor possui vendas registradas.';
    }
}

// Busca os vendedores com total de vendas calculado via COUNT das vendas
$vendedores = $pdo->query("
    SELECT v.*, COUNT(vd.id) AS total_vendas_real,
           COALESCE(SUM(vd.valor_total), 0) AS total_valor
    FROM vendedor v
    LEFT JOIN venda vd ON v.id = vd.vendedor_id
    GROUP BY v.id
    ORDER BY v.nome ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Vendedores</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Vendedores</h1>
            <p>Gerencie a equipe de vendas</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2><i class="ri-user-add-line"></i> Novo Vendedor</h2>
            </div>
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do Vendedor *</label>
                        <input type="text" name="nome" placeholder="Nome completo" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" id="tel_vend" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                </div>
                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-save-line"></i> Cadastrar Vendedor
                </button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="ri-user-star-line"></i> Equipe de Vendas</h2>
            </div>
            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Telefone</th>
                            <th>Qtd. Vendas</th>
                            <th>Total Vendido</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vendedores)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:30px;">Nenhum vendedor cadastrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($vendedores as $v): ?>
                                <tr>
                                    <td><?php echo $v['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($v['nome']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($v['telefone']); ?></td>
                                    <td><?php echo $v['total_vendas_real']; ?> vendas</td>
                                    <td>R$ <?php echo number_format($v['total_valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <a href="?excluir=<?php echo $v['id']; ?>"
                                           class="btn btn-perigo btn-sm"
                                           onclick="return confirm('Excluir este vendedor?')">
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

<script>
document.getElementById('tel_vend').addEventListener('input', function() {
    let v = this.value.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '($1) $2');
    v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
    this.value = v;
});
</script>

</body>
</html>
