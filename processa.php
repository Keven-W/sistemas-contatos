<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/email.php';

// Aceita somente POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('index.php');
}

// Proteção CSRF
if (!csrf_valido($_POST['csrf_token'] ?? null)) {
    redirecionar('index.php?status=erro_csrf');
}

// Honeypot: se o campo oculto foi preenchido, é um robô. Finge sucesso e não grava nada.
if (!empty($_POST['website'])) {
    redirecionar('index.php?status=sucesso');
}

$dados = [
    'nome'     => trim((string) ($_POST['nome'] ?? '')),
    'email'    => trim((string) ($_POST['email'] ?? '')),
    'telefone' => trim((string) ($_POST['telefone'] ?? '')),
    'mensagem' => trim((string) ($_POST['mensagem'] ?? '')),
];

$erros = validar_contato($dados);

if ($erros) {
    $_SESSION['erros'] = $erros;
    $_SESSION['old']   = $dados;
    redirecionar('index.php?status=erro_validacao#form-contato');
}

// 1) Salva o contato no banco (prepared statement)
try {
    $pdo = conexao();

    $stmt = $pdo->prepare(
        'INSERT INTO contatos (nome, email, telefone, mensagem)
         VALUES (:nome, :email, :telefone, :mensagem)'
    );

    $stmt->execute([
        ':nome'     => $dados['nome'],
        ':email'    => $dados['email'],
        ':telefone' => $dados['telefone'],
        ':mensagem' => $dados['mensagem'],
    ]);
} catch (Throwable $excecao) {
    // Detalhes técnicos ficam apenas no log do servidor
    error_log('[processa] Falha ao salvar contato: ' . $excecao->getMessage());
    $_SESSION['old'] = $dados;
    redirecionar('index.php?status=erro_banco#form-contato');
}

// 2) Envia o e-mail. O contato já está salvo, mesmo que o envio falhe.
$emailEnviado = enviar_email_contato($dados);

unset($_SESSION['old'], $_SESSION['erros']);

redirecionar('index.php?status=' . ($emailEnviado ? 'sucesso' : 'sucesso_sem_email'));
