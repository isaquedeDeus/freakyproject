<?php
// ============================================================
// ARQUIVO: admin/fornecedores.php
// FUNÇÃO: Cadastro e listagem de fornecedores (Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin();
require_once '../includes/conexao.php';

$mensagem = '';
$erro      = '';

// -----------------------------------------------
// PROCESSAMENTO DO FORMULÁRIO (POST)
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Captura os dados do fornecedor
    $nome     = trim($_POST['nome']     ?? '');
    $cnpj     = trim($_POST['cnpj']     ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // Dados de endereço do fornecedor
    $rua        = trim($_POST['rua']        ?? '');
    $numero     = trim($_POST['numero']     ?? '');
    $bairro     = trim($_POST['bairro']     ?? '');
    $cidade     = trim($_POST['cidade']     ?? '');
    $estado     = trim($_POST['estado']     ?? '');
    $cep        = trim($_POST['cep']        ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    if (empty($nome) || empty($cnpj) || empty($cidade)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            // 1. Insere o endereço do fornecedor
            $stmt = $pdo->prepare("
                INSERT INTO endereco (rua, numero, bairro, cidade, estado, cep, complemento)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$rua, $numero, $bairro, $cidade, $estado, $cep, $complemento]);
            $endereco_id = $pdo->lastInsertId();

            // 2. Insere o fornecedor com o endereco_id gerado
            $stmt = $pdo->prepare("
                INSERT INTO fornecedor (nome, cnpj, telefone, endereco_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $cnpj, $telefone, $endereco_id]);

            $mensagem = 'Fornecedor cadastrado com sucesso!';

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = 'CNPJ já cadastrado no sistema.';
            } else {
                $erro = 'Erro ao cadastrar: ' . $e->getMessage();
            }
        }
    }
}

// -----------------------------------------------
// EXCLUSÃO
// -----------------------------------------------
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    try {
        $stmt = $pdo->prepare("SELECT endereco_id FROM fornecedor WHERE id = ?");
        $stmt->execute([$id]);
        $forn = $stmt->fetch();

        if ($forn) {
            $pdo->prepare("DELETE FROM fornecedor WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM endereco WHERE id = ?")->execute([$forn['endereco_id']]);
            $mensagem = 'Fornecedor excluído com sucesso!';
        }
    } catch (PDOException $e) {
        $erro = 'Não é possível excluir: fornecedor possui produtos vinculados.';
    }
}

// -----------------------------------------------
// LISTAGEM
// -----------------------------------------------
$fornecedores = $pdo->query("
    SELECT f.*, e.cidade, e.estado, e.rua, e.numero
    FROM fornecedor f
    JOIN endereco e ON f.endereco_id = e.id
    ORDER BY f.nome ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Fornecedores</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Fornecedores</h1>
            <p>Gerencie os fornecedores da loja</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-building-line"></i> Novo Fornecedor</h2>
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Razão Social / Nome *</label>
                        <input type="text" name="nome" placeholder="Nome do fornecedor" required>
                    </div>

                    <div class="form-group">
                        <label>CNPJ *</label>
                        <!-- id="cnpj" para aplicar a máscara com JavaScript -->
                        <input type="text" name="cnpj" id="cnpj" placeholder="00.000.000/0000-00" maxlength="18" required>
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" id="tel_forn" placeholder="(00) 00000-0000" maxlength="15">
                    </div>

                </div>

                <p style="font-weight:600; color:#555; margin:10px 0; font-size:0.9rem;">
                    <i class="ri-map-pin-line"></i> Endereço do Fornecedor
                </p>

                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Rua / Logradouro</label>
                        <input type="text" name="rua" placeholder="Nome da rua">
                    </div>

                    <div class="form-group">
                        <label>Número</label>
                        <input type="number" name="numero" placeholder="Ex: 100">
                    </div>

                    <div class="form-group">
                        <label>Complemento</label>
                        <input type="text" name="complemento" placeholder="Sala, galpão... (opcional)">
                    </div>

                    <div class="form-group">
                        <label>Bairro</label>
                        <input type="text" name="bairro" placeholder="Bairro">
                    </div>

                    <div class="form-group">
                        <label>Cidade *</label>
                        <input type="text" name="cidade" placeholder="Cidade" required>
                    </div>

                    <div class="form-group">
                        <label>Estado</label>
                        <select name="estado">
                            <option value="">Selecione...</option>
                            <?php
                            $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            foreach ($estados as $uf):
                            ?>
                                <option value="<?php echo $uf; ?>"><?php echo $uf; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>CEP</label>
                        <input type="text" name="cep" id="cep_forn" placeholder="00000-000" maxlength="9">
                    </div>

                </div>

                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-save-line"></i> Cadastrar Fornecedor
                </button>

            </form>
        </div>

        <!-- LISTAGEM -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-building-line"></i> Fornecedores Cadastrados</h2>
                <span style="color:#888; font-size:0.9rem;"><?php echo count($fornecedores); ?> fornecedor(es)</span>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>Cidade/UF</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fornecedores)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; color:#aaa; padding:30px;">
                                    Nenhum fornecedor cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fornecedores as $f): ?>
                                <tr>
                                    <td><?php echo $f['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($f['nome']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($f['cnpj']); ?></td>
                                    <td><?php echo htmlspecialchars($f['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($f['cidade'] . '/' . $f['estado']); ?></td>
                                    <td>
                                        <a href="?excluir=<?php echo $f['id']; ?>"
                                           class="btn btn-perigo btn-sm"
                                           onclick="return confirm('Excluir este fornecedor?')">
                                            <i class="ri-delete-bin-line"></i> Excluir
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
// Máscara CNPJ: 00.000.000/0000-00
function mascaraCNPJ(v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/, '.$1/$2');
    v = v.replace(/(\d{4})(\d)/, '$1-$2');
    return v;
}

function mascaraTel(v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '($1) $2');
    v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
    return v;
}

function mascaraCEP(v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/(\d{5})(\d)/, '$1-$2');
    return v;
}

// Aplica as máscaras nos inputs correspondentes
document.getElementById('cnpj').addEventListener('input', function() {
    this.value = mascaraCNPJ(this.value);
});
document.getElementById('tel_forn').addEventListener('input', function() {
    this.value = mascaraTel(this.value);
});
document.getElementById('cep_forn').addEventListener('input', function() {
    this.value = mascaraCEP(this.value);
});
</script>

</body>
</html>
