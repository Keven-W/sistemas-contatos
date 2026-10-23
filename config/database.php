<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

/**
 * Retorna uma conexão PDO reutilizável com o MySQL.
 * As credenciais vêm do arquivo .env (variáveis DB_*).
 *
 * @throws RuntimeException se a configuração estiver incompleta
 * @throws PDOException se não for possível conectar
 */
function conexao(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host    = env('DB_HOST');
    $porta   = env('DB_PORT', '3306');
    $nome    = env('DB_NAME');
    $usuario = env('DB_USER');
    $senha   = env('DB_PASS', '');

    if ($host === null || $nome === null || $usuario === null) {
        throw new RuntimeException('Configuração do banco de dados incompleta (verifique o arquivo .env).');
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $porta, $nome);

    $pdo = new PDO($dsn, $usuario, $senha, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 10,
    ]);

    return $pdo;
}
