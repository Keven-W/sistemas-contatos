<?php
declare(strict_types=1);

/**
 * Inicialização comum do sistema:
 * - carrega o arquivo .env (fora do código PHP);
 * - configura exibição de erros (sem vazar detalhes em produção);
 * - inicia a sessão com cookies seguros.
 */

function carregar_env(string $arquivo): void
{
    if (!is_file($arquivo) || !is_readable($arquivo)) {
        return;
    }

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return;
    }

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }

        [$chave, $valor] = array_map('trim', explode('=', $linha, 2));

        $tamanho = strlen($valor);
        if ($tamanho >= 2 && ($valor[0] === '"' || $valor[0] === "'") && $valor[$tamanho - 1] === $valor[0]) {
            $valor = substr($valor, 1, -1);
        }

        if (!array_key_exists($chave, $_ENV) && getenv($chave) === false) {
            putenv($chave . '=' . $valor);
            $_ENV[$chave] = $valor;
        }
    }
}

function env(string $chave, ?string $padrao = null): ?string
{
    if (isset($_ENV[$chave]) && $_ENV[$chave] !== '') {
        return (string) $_ENV[$chave];
    }

    $valor = getenv($chave);

    return ($valor !== false && $valor !== '') ? $valor : $padrao;
}

// Carrega o .env que fica na raiz do projeto (um nível acima de /config).
carregar_env(dirname(__DIR__) . '/.env');

// Por padrão nenhum erro é exibido ao visitante (evita exposição de detalhes).
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

// Em ambiente local (XAMPP) os erros podem ser exibidos para facilitar o desenvolvimento.
if (env('APP_ENV', 'producao') === 'local') {
    ini_set('display_errors', '1');
}

if (session_status() === PHP_SESSION_NONE) {
    session_name('contatos_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

require_once __DIR__ . '/../includes/funcoes.php';
