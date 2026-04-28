# Sistema de Loja — Documentação de Instalação

## Estrutura de Arquivos

```
loja/
├── index.php                  ← Página de Login
├── sair.php                   ← Logout
├── assets/
│   └── css/
│       └── estilo.css         ← Estilos visuais do sistema
├── includes/
│   ├── conexao.php            ← Conexão com o banco de dados
│   ├── auth.php               ← Funções de autenticação/sessão
│   ├── sidebar_admin.php      ← Menu lateral do Admin
│   └── sidebar_vendedor.php   ← Menu lateral do Vendedor
├── admin/
│   ├── painel.php             ← Dashboard do Admin
│   ├── clientes.php           ← Cadastro de Clientes
│   ├── fornecedores.php       ← Cadastro de Fornecedores
│   ├── produtos.php           ← Cadastro de Produtos
│   ├── vendedores.php         ← Cadastro de Vendedores
│   ├── estoque.php            ← Controle de Estoque
│   ├── vendas.php             ← Registro de Vendas
│   └── usuarios.php           ← Gerenciamento de Usuários
└── vendedor/
    ├── painel.php             ← Dashboard do Vendedor
    ├── clientes.php           ← Cadastro de Clientes (restrito)
    ├── vendas.php             ← Registrar Venda
    └── minhas_vendas.php      ← Histórico de Vendas
```

---

## Passo a Passo para Instalar

### 1. Instale o XAMPP
- Baixe em: https://www.apachefriends.org/
- Instale e abra o XAMPP Control Panel
- Inicie os serviços **Apache** e **MySQL**

### 2. Coloque o projeto na pasta correta
- Copie a pasta `loja` para: `C:\xampp\htdocs\loja`

### 3. Importe o banco de dados
- Abra o navegador e acesse: `http://localhost/phpmyadmin`
- Clique em **"Novo"** para criar o banco de dados com o nome `loja`
- Selecione o banco `loja` criado
- Clique na aba **"Importar"**
- Clique em **"Escolher arquivo"** e selecione o arquivo `loja.sql`
- Clique em **"Executar"**

### 4. Configure a conexão (se necessário)
- Abra o arquivo `includes/conexao.php`
- Verifique se os dados estão corretos:
  ```php
  $host   = 'localhost';
  $dbname = 'loja_system';
  $usuario = 'root';
  $senha  = '';        // No XAMPP padrão a senha é vazia
  ```

### 5. Acesse o sistema
- Abra o navegador e acesse: `http://localhost/loja`

---

## Credenciais de Acesso (já no banco de dados)

| Usuário   | Senha     | Tipo       | Acesso                        |
|-----------|-----------|------------|-------------------------------|
| admin     | admin     | Admin      | Acesso total ao sistema       |
| vendedor  | vendedor  | Vendedor   | Clientes, Vendas e Histórico  |

---

## Fluxo Recomendado para Testar

1. **Login como Admin**
2. Cadastrar um **Fornecedor** (necessário para criar produto)
3. Cadastrar um **Produto** (vincular ao fornecedor)
4. Adicionar o produto ao **Estoque** (com quantidade e preço)
5. Cadastrar um **Vendedor** (equipe de vendas)
6. Cadastrar um **Cliente**
7. Registrar uma **Venda**
8. Ver estatísticas no **Dashboard**

---

## Diferenças de Acesso

### Admin vê e pode fazer:
- Dashboard completo com todos os dados
- Cadastrar/excluir Clientes
- Cadastrar/excluir Fornecedores
- Cadastrar/excluir Produtos
- Controlar Estoque (quantidades e preços)
- Cadastrar/excluir Vendedores
- Registrar e ver todas as Vendas
- Criar e excluir Usuários do sistema

### Vendedor vê e pode fazer:
- Dashboard simplificado
- Cadastrar Clientes
- Registrar Vendas
- Ver Histórico de Vendas

---

## Tecnologias Utilizadas

- **PHP 8+** — Back-end e lógica do sistema
- **MySQL / MariaDB** — Banco de dados
- **PDO** — Conexão segura ao banco com prepared statements
- **HTML5 + CSS3** — Interface do usuário
- **JavaScript** — Máscaras de input e cálculos automáticos
- **Remix Icon** — Ícones (carregados via CDN)
- **Google Fonts** — Tipografia (Syne + DM Sans)

---

## Segurança Implementada

- Senhas armazenadas com **bcrypt** (`password_hash`)
- Consultas SQL com **Prepared Statements** (proteção contra SQL Injection)
- Dados de saída com **htmlspecialchars** (proteção contra XSS)
- Controle de sessão com verificação de tipo de usuário em cada página
- Vendedor não acessa páginas de Admin mesmo digitando a URL diretamente
