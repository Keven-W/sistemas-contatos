<?php
declare(strict_types=1);

/**
 * Cabeçalho comum. Variáveis esperadas (opcionais):
 * $titulo, $paginaAtiva
 */
$tituloPagina = e($titulo ?? 'Sistema de Contatos');
$pagina       = $paginaAtiva ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de contatos">
    <title><?= $tituloPagina ?> | Sistema de Contatos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topo">
    <div class="container topo-interno">
        <a href="index.php" class="marca">Sistema<span>Contatos</span></a>

        <nav class="menu" aria-label="Navegação principal">
            <a href="index.php" class="<?= $pagina === 'index' ? 'ativo' : '' ?>">Contato</a>

            <?php if (esta_logado()): ?>
                <a href="contatos.php" class="<?= $pagina === 'contatos' ? 'ativo' : '' ?>">Painel</a>
                <form method="post" action="logout.php" class="form-inline">
                    <?= csrf_campo() ?>
                    <button type="submit" class="botao botao-secundario botao-pequeno">Sair</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="<?= $pagina === 'login' ? 'ativo' : '' ?>">Área administrativa</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container conteudo">
