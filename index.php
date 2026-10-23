<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$mensagensStatus = [
    'sucesso' => [
        'tipo'  => 'sucesso',
        'texto' => 'Mensagem enviada com sucesso! Em breve entraremos em contato.',
    ],
    'sucesso_sem_email' => [
        'tipo'  => 'aviso',
        'texto' => 'Sua mensagem foi registrada, mas não foi possível enviar a notificação por e-mail. Tente novamente mais tarde, se necessário.',
    ],
    'erro_validacao' => [
        'tipo'  => 'erro',
        'texto' => 'Verifique os campos destacados e tente novamente.',
    ],
    'erro_csrf' => [
        'tipo'  => 'erro',
        'texto' => 'Sua sessão expirou. Recarregue a página e envie novamente.',
    ],
    'erro_banco' => [
        'tipo'  => 'erro',
        'texto' => 'Não foi possível salvar sua mensagem no momento. Tente novamente mais tarde.',
    ],
];

$status  = $_GET['status'] ?? '';
$alerta  = $mensagensStatus[$status] ?? null;
$erros   = $_SESSION['erros'] ?? [];
$valores = $_SESSION['old'] ?? [];
unset($_SESSION['erros'], $_SESSION['old']);

$titulo       = 'Fale Conosco';
$paginaAtiva  = 'index';

require __DIR__ . '/includes/header.php';
?>

<section class="cartao cartao-form">
    <div class="cabecalho-form">
        <h1>Entre em contato</h1>
        <p>Preencha o formulário abaixo. Responderemos o mais rápido possível.</p>
    </div>

    <?php if ($alerta): ?>
        <div class="alerta alerta-<?= e($alerta['tipo']) ?>" role="alert" id="alerta-status">
            <?= e($alerta['texto']) ?>
        </div>
    <?php endif; ?>

    <form id="form-contato" class="formulario" action="processa.php" method="post" novalidate>
        <?= csrf_campo() ?>

        <!-- Campo "armadilha" para robôs: invisível para pessoas -->
        <div class="honeypot" aria-hidden="true">
            <label for="website">Não preencha este campo</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="campo" data-campo="nome">
            <label for="nome">Nome completo</label>
            <input type="text" id="nome" name="nome" maxlength="100" required
                   placeholder="Seu nome"
                   value="<?= e($valores['nome'] ?? '') ?>"
                   <?= isset($erros['nome']) ? 'aria-invalid="true"' : '' ?>>
            <span class="erro-msg" id="erro-nome"><?= e($erros['nome'] ?? '') ?></span>
        </div>

        <div class="campo" data-campo="email">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="150" required
                   placeholder="voce@exemplo.com"
                   value="<?= e($valores['email'] ?? '') ?>"
                   <?= isset($erros['email']) ? 'aria-invalid="true"' : '' ?>>
            <span class="erro-msg" id="erro-email"><?= e($erros['email'] ?? '') ?></span>
        </div>

        <div class="campo" data-campo="telefone">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" maxlength="20" required
                   placeholder="(11) 99999-9999"
                   value="<?= e($valores['telefone'] ?? '') ?>"
                   <?= isset($erros['telefone']) ? 'aria-invalid="true"' : '' ?>>
            <span class="erro-msg" id="erro-telefone"><?= e($erros['telefone'] ?? '') ?></span>
        </div>

        <div class="campo" data-campo="mensagem">
            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" rows="6" maxlength="2000" required
                      placeholder="Escreva sua mensagem aqui..."
                      <?= isset($erros['mensagem']) ? 'aria-invalid="true"' : '' ?>><?= e($valores['mensagem'] ?? '') ?></textarea>
            <div class="rodape-campo">
                <span class="erro-msg" id="erro-mensagem"><?= e($erros['mensagem'] ?? '') ?></span>
                <span class="contador" id="contador">0/2000</span>
            </div>
        </div>

        <button type="submit" class="botao botao-primario botao-bloco" id="botao-enviar">
            Enviar mensagem
        </button>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
