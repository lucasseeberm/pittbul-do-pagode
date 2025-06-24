<?php
session_start();

// Protege a página: se não estiver logado, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php'; // Conexão com o BD ($pdo_usuarios)

$mensagem = '';
$erro = false;
$id_usuario = $_SESSION['usuario_id'];

// Lógica para processar o formulário quando enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados do formulário
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    // Validação básica
    if (empty($nome) || empty($email)) {
        $mensagem = "Nome e e-mail são obrigatórios.";
        $erro = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Formato de e-mail inválido.";
        $erro = true;
    } else {
        try {
            // Verifica se o novo e-mail já está em uso por OUTRO usuário
            $stmt = $pdo_usuarios->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id_usuario]);
            if ($stmt->fetch()) {
                $mensagem = "Este e-mail já está em uso por outra conta.";
                $erro = true;
            } else {
                // Se tudo estiver certo, atualiza nome e e-mail
                $stmt = $pdo_usuarios->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $id_usuario]);

                // Atualiza o nome na sessão para que apareça na saudação
                $_SESSION['usuario_nome'] = $nome;

                $mensagem = "Perfil atualizado com sucesso!";
            }
        } catch (\PDOException $e) {
            $mensagem = "Erro ao atualizar o perfil.";
            $erro = true;
        }
    }
}

// Lógica para buscar os dados atuais para preencher o formulário (GET)
try {
    $stmt = $pdo_usuarios->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        // Caso raro, mas seguro
        die("Usuário não encontrado.");
    }
} catch (\PDOException $e) {
    die("Erro ao buscar dados do usuário.");
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Editar Perfil</h2>
                <hr>

                <?php if (!empty($mensagem)): ?>
                    <div class="alert <?php echo $erro ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo $mensagem; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_profile.php" method="post">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                    <a href="profile.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>