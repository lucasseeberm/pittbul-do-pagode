<?php
// Inclui o arquivo de conexão
require_once 'config/database.php'; // $pdo_usuarios estará disponível aqui

$mensagem = '';
$sucesso = false;

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "Erro: Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Erro: Formato de e-mail inválido.";
    } else {
        try {
            // Verifica se o e-mail já existe
            $stmt = $pdo_usuarios->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $mensagem = "Erro: Este e-mail já está cadastrado.";
            } else {
                // Criptografa a senha com segurança
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Insere o novo usuário no banco de dados
                $stmt = $pdo_usuarios->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
                if ($stmt->execute([$nome, $email, $senha_hash])) {
                    $mensagem = "Conta criada com sucesso! Você já pode fazer login.";
                    $sucesso = true;
                }
            }
        } catch (\PDOException $e) {
            // Em um sistema real, logaríamos o erro em vez de exibi-lo
            $mensagem = "Erro de banco de dados: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Conta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Criar Nova Conta</h2>
                <hr>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert <?php echo $sucesso ? 'alert-success' : 'alert-danger'; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$sucesso): ?>
                <form action="register.php" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Criar Conta</button>
                </form>
                <?php endif; ?>
                
                <div class="mt-3">
                    <a href="index.php">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>