<?php
// ============================================================
// ARQUIVO: admin/clientes.php
// FUNÇÃO: Cadastro, listagem e exclusão de clientes (Admin)
// ============================================================

require_once '../includes/auth.php';
verificarAdmin(); // Somente admin acessa este arquivo
require_once '../includes/conexao.php';

$mensagem = ''; // Armazena mensagem de sucesso
$erro      = ''; // Armazena mensagem de erro

// -----------------------------------------------
// PROCESSAMENTO: Formulário enviado via POST
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Captura e limpa todos os campos do formulário
    $nome     = trim($_POST['nome']     ?? '');   //$POST: define o que é a variavel
    $cpf      = trim($_POST['cpf']      ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    // Campos do endereço
    $rua        = trim($_POST['rua']        ?? '');
    $numero     = trim($_POST['numero']     ?? '');
    $bairro     = trim($_POST['bairro']     ?? '');
    $cidade     = trim($_POST['cidade']     ?? '');
    $estado     = trim($_POST['estado']     ?? '');
    $cep        = trim($_POST['cep']        ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // Valida se campos obrigatórios estão preenchidos
    if (empty($nome) || empty($cpf) || empty($rua) || empty($cidade)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            // PASSO 1: Insere o endereço primeiro (pois cliente precisa do ID do endereço)
            $stmt = $pdo->prepare("
                INSERT INTO endereco (rua, numero, bairro, cidade, estado, cep, complemento)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$rua, $numero, $bairro, $cidade, $estado, $cep, $complemento]);

            // Pega o ID do endereço recém inserido
            $endereco_id = $pdo->lastInsertId();

            // PASSO 2: Insere o cliente vinculado ao endereço
            $stmt = $pdo->prepare("
                INSERT INTO cliente (nome, cpf, telefone, endereco_id)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$nome, $cpf, $telefone, $endereco_id]);

            $mensagem = 'Cliente cadastrado com sucesso!';

        } catch (PDOException $e) {
            // Captura erro de CPF duplicado (unique key no banco)
            if ($e->getCode() == 23000) {
                $erro = 'CPF já cadastrado no sistema.';
            } else {
                $erro = 'Erro ao cadastrar: ' . $e->getMessage();
            }
        }
    }
}

// -----------------------------------------------
// EXCLUSÃO: Via GET com ?excluir=ID
// -----------------------------------------------
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir']; // (int) garante que é um número inteiro

    try {
        // Busca o endereco_id do cliente para excluir também o endereço
        $stmt = $pdo->prepare("SELECT id, endereco_id FROM cliente WHERE endereco_id = ?");
        $stmt->execute([$id]);
        $cli = $stmt->fetch();

        if ($cli) {

            $pdo->prepare("DELETE FROM venda WHERE cliente_id = ?")->execute([$cli['id']]);
            
            $pdo->prepare("DELETE FROM endereco WHERE id = ?")->execute([$cli['endereco_id']]);
            
            $pdo->prepare("DELETE FROM cliente WHERE id = ?")->execute([$cli['id']]);
            
            
            $mensagem = 'Cliente excluído com sucesso!';
        }
    } catch (PDOException $e) {
        $erro = 'Não é possível excluir: cliente possui vendas vinculadas.';
    }
}

// -----------------------------------------------
// LISTAGEM: Busca todos os clientes com endereço
// -----------------------------------------------
// JOIN une cliente com endereco para mostrar endereço completo
$clientes = $pdo->query("
    SELECT c.*, e.cidade, e.estado, e.rua, e.numero
    FROM cliente c
    JOIN endereco e ON c.endereco_id = e.id
    ORDER BY c.nome ASC -- Ordena por nome em ordem crescente
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – Clientes</title>
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<div class="layout">
    <?php require_once '../includes/sidebar_admin.php'; ?>

    <main class="conteudo">

        <div class="page-header">
            <h1>Clientes</h1>
            <p>Gerencie o cadastro de clientes</p>
        </div>

        <!-- Exibe mensagens de sucesso/erro -->
        <?php if ($mensagem): ?>
            <div class="alerta alerta-sucesso">
                <i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alerta alerta-erro">
                <i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <!-- ============================
             FORMULÁRIO DE CADASTRO
             ============================ -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-user-add-line"></i> Novo Cliente</h2>
            </div>

            <form method="POST" action="">

                <!-- Dados pessoais em 2 colunas -->
                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Nome Completo *</label>
                        <input type="text" name="nome" placeholder="Nome do cliente" required>
                    </div>

                    <div class="form-group">
                        <label>CPF *</label>
                        <!-- id="cpf" para o JavaScript de máscara funcionar -->
                        <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" maxlength="14" required>
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" maxlength="15">
                    </div>

                </div>

                <!-- Separador visual de endereço -->
                <p style="font-weight:600; color:#555; margin:10px 0; font-size:0.9rem;">
                    <i class="ri-map-pin-line"></i> Endereço
                </p>

                <div class="form-grid">

                    <div class="form-group col-2">
                        <label>Rua / Logradouro *</label>
                        <input type="text" name="rua" placeholder="Nome da rua" required>
                    </div>

                    <div class="form-group">
                        <label>Número *</label>
                        <input type="number" name="numero" placeholder="Ex: 123" required>
                    </div>

                    <div class="form-group">
                        <label>Complemento</label>
                        <input type="text" name="complemento" placeholder="Apto, bloco... (opcional)">
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
                        <label>Estado *</label>
                        <!-- Select com todos os estados do Brasil -->
                        <select name="estado" required>
                            <option value="">Selecione...</option>
                            <?php
                            // Array com siglas dos estados
                            $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            foreach ($estados as $uf):
                            ?>
                                <option value="<?php echo $uf; ?>"><?php echo $uf; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>CEP</label>
                        <input type="text" name="cep" id="cep" placeholder="00000-000" maxlength="9">
                    </div>

                </div>

                <button type="submit" class="btn btn-primario" style="width:auto; margin-top:8px;">
                    <i class="ri-save-line"></i> Cadastrar Cliente
                </button>

            </form>
        </div>

        <!-- ============================
             TABELA DE LISTAGEM
             ============================ -->
        <div class="card">
            <div class="card-header">
                <h2><i class="ri-group-line"></i> Clientes Cadastrados</h2>
                <!-- Exibe o total -->
                <span style="color:#888; font-size:0.9rem;"><?php echo count($clientes); ?> cliente(s)</span>
            </div>

            <div class="tabela-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>Cidade/UF</th>
                            <th>Endereço</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:#aaa; padding:30px;">
                                    Nenhum cliente cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td><?php echo $c['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['cpf']); ?></td>
                                    <td><?php echo htmlspecialchars($c['telefone']); ?></td>
                                    <td><?php echo htmlspecialchars($c['cidade'] . '/' . $c['estado']); ?></td>
                                    <td><?php echo htmlspecialchars($c['rua'] . ', ' . $c['numero']); ?></td>
                                    <td>
                                        <!-- Link de exclusão com confirmação JavaScript -->
                                        <a href="?excluir=<?php echo $c['id']; ?>"
                                           class="btn btn-perigo btn-sm"
                                           onclick="return confirm('Excluir este cliente?')">
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

<!-- Script para máscaras de CPF, telefone e CEP -->
<script>
// Função que aplica máscara conforme o usuário digita
function mascara(el, func) {
    el.addEventListener('input', function() {
        this.value = func(this.value);
    });
}

// Máscara CPF: 000.000.000-00
function mascaraCPF(v) {
    v = v.replace(/\D/g, '');            // Remove tudo que não é dígito
    v = v.replace(/(\d{3})(\d)/, '$1.$2');     // Coloca ponto depois do 3º dígito
    v = v.replace(/(\d{3})(\d)/, '$1.$2');     // Coloca ponto depois do 6º dígito
    v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Coloca traço antes dos últimos 2 dígitos
    return v;
}

// Máscara Telefone: (00) 00000-0000
function mascaraTel(v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/^(\d{2})(\d)/, '($1) $2');
    v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
    return v;
}

// Máscara CEP: 00000-000
function mascaraCEP(v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/(\d{5})(\d)/, '$1-$2');
    return v;
}

// Aplica as máscaras nos inputs
mascara(document.getElementById('cpf'),      mascaraCPF);
mascara(document.getElementById('telefone'), mascaraTel);
mascara(document.getElementById('cep'),      mascaraCEP);
</script>

</body>
</html>
