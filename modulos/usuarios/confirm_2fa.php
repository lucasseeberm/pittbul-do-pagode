<?php
session_start();
if (!isset($_SESSION['usuario_id'], $_SESSION['2fa_temp_secret'], $_POST['code'])) {
    header('Location: profile.php');
    exit();
}

require_once 'lib/GoogleAuthenticator.php';
require_once 'config/database.php';
$ga = new PHPGangsta_GoogleAuthenticator();

$secret = $_SESSION['2fa_temp_secret'];
$code = $_POST['code'];

// Verifica se o código é válido
$checkResult = $ga->verifyCode($secret, $code, 2); // 2 = tolerância de 2 * 30s

if ($checkResult) {
    // Código correto: Salva a chave no banco e ativa o 2FA
    $sql = "UPDATE usuarios SET google_auth_secret = ?, is_2fa_enabled = TRUE WHERE id = ?";
    $stmt = $pdo_usuarios->prepare($sql);
    $stmt->execute([$secret, $_SESSION['usuario_id']]);
    
    unset($_SESSION['2fa_temp_secret']); // Limpa a chave temporária
    header('Location: profile.php?status=2fa_success');
} else {
    // Código incorreto: volta para a página de setup com erro
    header('Location: setup_2fa.php?status=code_error');
}
exit();