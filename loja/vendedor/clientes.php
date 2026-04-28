<?php
// ============================================================
// ARQUIVO: vendedor/clientes.php
// FUNÇÃO: Cadastro de clientes pelo vendedor
// ============================================================

// Importa a autenticação e verifica se o usuário é vendedor
require_once '../includes/auth.php';
verificarVendedor();

// Importa a conexão com o banco de dados ($pdo)
require_once '../includes/conexao.php';

// Inicializa variáveis de feedback para o usuário
$mensagem = '';
$erro = '';

// Verifica se o formulário foi enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe e sanitiza os dados do formulário, removendo espaços em branco extras
    $nome = trim($_POST['nome'] ?? '');
    $cpf = trim($_POST['cpf'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $rua = trim($_POST['rua'] ?? '');
    $numero = trim($_POST['numero'] ?? '');
    $bairro = trim($_POST['bairro'] ?? '');
    $cidade = trim($_POST['cidade'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $cep = trim($_POST['cep'] ?? '');
    $complemento = trim($_POST['complemento'] ?? '');

    // Validação básica: verifica se campos obrigatórios estão vazios
    if (empty($nome) || empty($cpf) || empty($cidade)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } else {
        try {
            // Inicia uma transação ou usa prepared statements para segurança contra SQL Injection
            
            // 1. Insere o endereço primeiro
            $stmt = $pdo->prepare("INSERT INTO endereco (rua, numero, bairro, cidade, estado, cep, complemento) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$rua, $numero, $bairro, $cidade, $estado, $cep, $complemento]);
            
            // Pega o ID do endereço recém-criado
            $endereco_id = $pdo->lastInsertId();
            
            // 2. Insere o cliente vinculado ao endereço
            $stmt = $pdo->prepare("INSERT INTO cliente (nome, cpf, telefone, endereco_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $cpf, $telefone, $endereco_id]);
            
            $mensagem = 'Cliente cadastrado com sucesso!';
        } catch (PDOException $e) {
            // Trata erros, especificamente CPF duplicado (código 23000)
            if ($e->getCode() == 23000) {
                $erro = 'CPF já cadastrado.';
            } else {
                $erro = 'Erro: ' . $e->getMessage();
            }
        }
    }
}

// Busca todos os clientes para listar na tabela, com JOIN para pegar a cidade
$clientes = $pdo->query("
    SELECT c.*, e.cidade, e.estado 
    FROM cliente c 
    JOIN endereco e ON c.endereco_id = e.id 
    ORDER BY c.nome ASC 
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor – Clientes</title>
    <!-- CSS Externo -->
    <link rel="stylesheet" href="../assets/css/estilo.css">
    <!-- Ícones Remixicon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    <div class="layout">
        <?php require_once '../includes/sidebar_vendedor.php'; // Inclui menu lateral ?>
        
        <main class="conteudo">
            <div class="page-header">
                <h1>Clientes</h1>
                <p>Cadastre e consulte clientes</p>
            </div>

            <!-- Exibe mensagem de sucesso, se houver -->
            <?php if ($mensagem): ?>
                <div class="alerta alerta-sucesso"><i class="ri-checkbox-circle-line"></i> <?php echo htmlspecialchars($mensagem); ?></div>
            <?php endif; ?>

            <!-- Exibe mensagem de erro, se houver -->
            <?php if ($erro): ?>
                <div class="alerta alerta-erro"><i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <!-- Card de Cadastro -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="ri-user-add-line"></i> Novo Cliente</h2>
                </div>
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group col-2">
                            <label>Nome Completo *</label>
                            <input type="text" name="nome" placeholder="Nome do cliente" required>
                        </div>
                        <div class="form-group">
                            <label>CPF *</label>
                            <!-- ID 'cpf' usado para a máscara JS -->
                            <input type="text" name="cpf" id="cpf" placeholder="000.000.000-00" maxlength="14" required>
                        </div>
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="telefone" placeholder="(00) 00000-0000" maxlength="15">
                        </div>
                        <div class="form-group col-2">
                            <label>Rua *</label>
                            <input type="text" name="rua" placeholder="Nome da rua" required>
                        </div>
                        <div class="form-group">
                            <label>Número</label>
                            <input type="number" name="numero" placeholder="Ex: 123">
                        </div>
                        <div class="form-group">
                            <label>Complemento</label>
                            <input type="text" name="complemento" placeholder="Apto, bloco...">
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
                                // Gera as opções do select de Estados (UF)
                                $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                                foreach ($estados as $uf): ?>
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

            <!-- Card de Listagem -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="ri-group-line"></i> Clientes Cadastrados</h2>
                    <!-- Conta quantos clientes foram retornados -->
                    <span style="color:#888; font-size:0.9rem;"><?php echo count($clientes); ?> cliente(s)</span>
                </div>
                <div class="tabela-wrapper">
                    <table>
                        <thead>
                            <tr><th>#</th><th>Nome</th><th>CPF</th><th>Telefone</th><th>Cidade/UF</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">Nenhum cliente ainda.</td></tr>
                            <?php else: ?>
                                <?php 
                                // Loop para listar cada cliente
                                foreach ($clientes as $c): ?>
                                    <tr>
                                        <td><?php echo $c['id']; ?></td>
                                        <!-- htmlspecialchars previne XSS -->
                                        <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['cpf']); ?></td>
                                        <td><?php echo htmlspecialchars($c['telefone']); ?></td>
                                        <td><?php echo htmlspecialchars($c['cidade'] . '/' . $c['estado']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript para Máscaras de Input -->
    <script>
        // Função genérica para aplicar máscara em um elemento
        function mascara(el, func) {
            el.addEventListener('input', function() {
                this.value = func(this.value);
            });
        }
        
        // Máscara para CPF: 000.000.000-00
        function mascaraCPF(v) {
            v = v.replace(/\D/g, ''); // Remove tudo que não é dígito
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return v;
        }
        
        // Máscara para Telefone: (00) 00000-0000
        function mascaraTel(v) {
            v = v.replace(/\D/g, '');
            v = v.replace(/^(\d{2})(\d)/, '($1) $2');
            v = v.replace(/(\d{5})(\d{1,4})$/, '$1-$2');
            return v;
        }
        
        // Máscara para CEP: 00000-000
        function mascaraCEP(v) {
            v = v.replace(/\D/g, '');
            v = v.replace(/(\d{5})(\d)/, '$1-$2');
            return v;
        }
        
        // Aplica as funções nos campos correspondentes pelo ID
        mascara(document.getElementById('cpf'), mascaraCPF);
        mascara(document.getElementById('telefone'), mascaraTel);
        mascara(document.getElementById('cep'), mascaraCEP);
    </script>
</body>
</html>
