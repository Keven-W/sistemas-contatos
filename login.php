<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

if (esta_logado()) {
    redirecionar('contatos.php');
}

$erro         = '';
$bloqueadoAte = (int) ($_SESSION['login_bloqueado_ate'] ?? 0);

if (isset($_GET['expirou'])) {
    $erro = 'Sua sessão expirou por inatividade. Faça login novamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido($_POST['csrf_token'] ?? null)) {
        $erro = 'Sessão inválida. Recarregue a página e tente novamente.';
    } elseif (time() < $bloqueadoAte) {
        $erro = 'Muitas tentativas incorretas. Aguarde alguns minutos e tente novamente.';
    } else {
        $usuario = trim((string) ($_POST['usuario'] ?? ''));
        $senha   = (string) ($_POST['senha'] ?? '');

        $adminUsuario = (string) env('ADMIN_USER', '');
        $adminHash    = (string) env('ADMIN_PASS_HASH', '');

        $credenciaisValidas = $adminUsuario !== ''
            && $adminHash !== ''
            && hash_equals($adminUsuario, $usuario)
            && password_verify($senha, $adminHash);

        if ($credenciaisValidas) {
            // Regenera o ID da sessão para evitar fixação de sessão
            session_regenerate_id(true);

            $_SESSION['admin_logado']  = true;
            $_SESSION['ultimo_acesso'] = time();
            $_SESSION['tentativas']    = 0;
            unset($_SESSION['login_bloqueado_ate']);

            redirecionar('contatos.php');
        }

        $_SESSION['tentativas'] = (int) ($_SESSION['tentativas'] ?? 0) + 1;

        if ($_SESSION['tentativas'] >= 5) {
            $_SESSION['login_bloqueado_ate'] = time() + 300; // 5 minutos
            $_SESSION['tentativas'] = 0;
            $erro = 'Muitas tentativas incorretas. Aguarde alguns minutos e tente novamente.';
        } else {
            $erro = 'Usuário ou senha inválidos.';
        }
    }
}

$titulo      = 'Login administrativo';
$paginaAtiva = 'login';

require __DIR__ . '/includes/header.php';
?>

<section class="cartao cartao-login">
    <h1>Área administrativa</h1>
    <p class="subtitulo">Acesso restrito.</p>

    <?php if ($erro !== ''): ?>
        <div class="alerta alerta-erro" role="alert"><?= e($erro) ?></div>
    <?php endif; ?>

    <form class="formulario" action="login.php" method="post" autocomplete="off">
        <?= csrf_campo() ?>

        <div class="campo">
            <label for="usuario">Usuário</label>
            <input type="text" id="usuario" name="usuario" maxlength="60" required autocomplete="username">
        </div>

        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" maxlength="200" required autocomplete="current-password">
        </div>

        <button type="submit" class="botao botao-primario botao-bloco">Entrar</button>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
