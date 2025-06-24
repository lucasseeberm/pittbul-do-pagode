<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit(); }

require_once 'lib/GoogleAuthenticator.php';
$ga = new PHPGangsta_GoogleAuthenticator();

// Gera uma nova chave secreta
$secret = $ga->createSecret();

// Armazena a chave na sessão TEMPORARIAMENTE até que o usuário confirme
$_SESSION['2fa_temp_secret'] = $secret;

// Gera a URL do QR Code
$qrCodeUrl = $ga->getQRCodeGoogleUrl('MinhaFarmacia', $secret);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurar Autenticação de Dois Fatores</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Configurar 2FA</h1>
        <p>Para ativar a autenticação de dois fatores, siga os passos abaixo:</p>
        <ol>
            <li>Abra seu aplicativo de autenticação (Google Authenticator, Authy, etc).</li>
            <li>Escaneie o QR Code abaixo com o seu aplicativo.</li>
            <li>Digite o código de 6 dígitos gerado pelo app no campo abaixo para confirmar.</li>
        </ol>

        <div class="text-center my-4">
            <img src="<?php echo $qrCodeUrl; ?>">
            <p class="mt-2">Ou insira a chave manualmente: <strong><?php echo $secret; ?></strong></p>
        </div>

        <form action="confirm_2fa.php" method="POST" class="col-md-6 offset-md-3">
            <div class="mb-3">
                <label for="code" class="form-label">Código de Verificação</label>
                <input type="text" name="code" id="code" class="form-control" required maxlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Ativar e Confirmar</button>
            <a href="profile.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>