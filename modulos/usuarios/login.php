<?php
// É essencial iniciar a sessão em todas as páginas que precisam de autenticação
session_start();

// Se o usuário já estiver logado, redireciona para o perfil
if (isset($_SESSION['usuario_id'])) {
    header('Location: profile.php');
    exit();
}

require_once 'config/database.php'; // $pdo_usuarios

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $mensagem = "Por favor, preencha o e-mail e a senha.";
    } else {
        try {
            $stmt = $pdo_usuarios->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            // Verifica se o usuário existe E se a senha está correta
            if ($usuario && password_verify($senha, $usuario['senha'])) {
                // Senha correta! Armazena os dados na sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                
                // Redireciona para uma página protegida
                header('Location: profile.php');
                exit();
            } else {
                // Usuário ou senha incorretos
                $mensagem = "E-mail ou senha inválidos.";
            }
        } catch (\PDOException $e) {
            $mensagem = "Erro no servidor. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Login de Usuário</h2>
                <hr>
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-danger"><?php echo $mensagem; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="senha" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required>
                    </div>
                    <button type="submit" class="btn btn-success">Entrar</button>
                </form>
                <div class="mt-3">
                    <a href="index.php">Voltar</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>