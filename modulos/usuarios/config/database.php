<?php
// Configuração do Banco de Dados de Usuários

// Lê as configurações das variáveis de ambiente fornecidas pelo Kubernetes
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
];

try {
    $pdo_usuarios = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Em um ambiente de container, é melhor logar o erro do que parar a execução
    error_log("Erro de conexão com o banco de dados de usuários: " . $e->getMessage());
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>