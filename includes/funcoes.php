<?php
declare(strict_types=1);

/**
 * Funções auxiliares: escape de saída, CSRF, autenticação e validação.
 */

/** Escapa texto para exibição em HTML (proteção contra XSS). */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Gera (ou reaproveita) o token CSRF da sessão. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/** Retorna o campo oculto com o token CSRF para formulários. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Confere o token CSRF enviado com o armazenado na sessão. */
function csrf_valido(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function esta_logado(): bool
{
    return !empty($_SESSION['admin_logado']);
}

/** Protege páginas administrativas: exige login e expira sessões inativas (30 min). */
function exigir_login(): void
{
    $tempoLimite = 1800;

    if (esta_logado() && (time() - (int) ($_SESSION['ultimo_acesso'] ?? 0)) > $tempoLimite) {
        $_SESSION['admin_logado'] = false;
        redirecionar('login.php?expirou=1');
    }

    if (!esta_logado()) {
        redirecionar('login.php');
    }

    $_SESSION['ultimo_acesso'] = time();
}

function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

/**
 * Valida os dados do formulário de contato.
 * Retorna um array associativo campo => mensagem de erro (vazio = válido).
 */
function validar_contato(array $dados): array
{
    $erros = [];

    $nome = $dados['nome'] ?? '';
    if ($nome === '') {
        $erros['nome'] = 'Informe seu nome.';
    } elseif (mb_strlen($nome) < 3 || mb_strlen($nome) > 100) {
        $erros['nome'] = 'O nome deve ter entre 3 e 100 caracteres.';
    } elseif (!preg_match('/^[\p{L}\s\'.\-]+$/u', $nome)) {
        $erros['nome'] = 'O nome contém caracteres inválidos.';
    }

    $email = $dados['email'] ?? '';
    if ($email === '') {
        $erros['email'] = 'Informe seu e-mail.';
    } elseif (mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros['email'] = 'Informe um e-mail válido.';
    }

    $telefone = $dados['telefone'] ?? '';
    $digitos = preg_replace('/\D/', '', $telefone);
    if ($telefone === '') {
        $erros['telefone'] = 'Informe seu telefone.';
    } elseif (
        mb_strlen($telefone) > 20
        || !preg_match('/^[0-9()+\s\-]+$/', $telefone)
        || strlen($digitos) < 10
        || strlen($digitos) > 13
    ) {
        $erros['telefone'] = 'Informe um telefone válido, com DDD.';
    }

    $mensagem = $dados['mensagem'] ?? '';
    if ($mensagem === '') {
        $erros['mensagem'] = 'Escreva sua mensagem.';
    } elseif (mb_strlen($mensagem) < 10 || mb_strlen($mensagem) > 2000) {
        $erros['mensagem'] = 'A mensagem deve ter entre 10 e 2000 caracteres.';
    }

    return $erros;
}
