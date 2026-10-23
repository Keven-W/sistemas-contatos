<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

exigir_login();

$contatos   = [];
$erroBanco  = false;

try {
    $stmt = conexao()->prepare(
        'SELECT id, nome, email, telefone, mensagem FROM contatos ORDER BY id DESC'
    );
    $stmt->execute();
    $contatos = $stmt->fetchAll();
} catch (Throwable $excecao) {
    error_log('[contatos] Falha ao consultar contatos: ' . $excecao->getMessage());
    $erroBanco = true;
}

$titulo      = 'Contatos recebidos';
$paginaAtiva = 'contatos';

require __DIR__ . '/includes/header.php';
?>

<section class="cabecalho-pagina">
    <div>
        <h1>Contatos recebidos</h1>
        <p class="subtitulo">Total: <strong><?= count($contatos) ?></strong></p>
    </div>
</section>

<?php if ($erroBanco): ?>
    <div class="alerta alerta-erro" role="alert">
        Não foi possível carregar os contatos no momento. Tente novamente mais tarde.
    </div>
<?php elseif (empty($contatos)): ?>
    <div class="cartao vazio">
        <p>Nenhum contato cadastrado até o momento.</p>
    </div>
<?php else: ?>
    <div class="cartao tabela-wrapper">
        <table class="tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                    <th>Mensagem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contatos as $contato): ?>
                    <tr>
                        <td data-label="ID"><?= e((string) $contato['id']) ?></td>
                        <td data-label="Nome"><?= e($contato['nome']) ?></td>
                        <td data-label="E-mail"><a href="mailto:<?= e($contato['email']) ?>"><?= e($contato['email']) ?></a></td>
                        <td data-label="Telefone"><?= e($contato['telefone']) ?></td>
                        <td data-label="Mensagem" class="coluna-mensagem"><?= nl2br(e($contato['mensagem'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
