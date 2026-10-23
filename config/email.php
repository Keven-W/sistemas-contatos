<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Envia por SMTP o e-mail com os dados do contato.
 * Todas as credenciais vêm do arquivo .env.
 *
 * Retorna true em caso de sucesso e false em caso de falha
 * (a falha é registrada no log do servidor, sem expor detalhes ao visitante).
 */
function enviar_email_contato(array $contato): bool
{
    $autoload = dirname(__DIR__) . '/vendor/autoload.php';

    if (!is_file($autoload)) {
        error_log('[email] vendor/autoload.php não encontrado. Execute: composer install');
        return false;
    }

    require_once $autoload;

    $host          = env('SMTP_HOST');
    $porta         = (int) env('SMTP_PORT', '0');
    $usuario       = env('SMTP_USER');
    $senha         = env('SMTP_PASS');
    $seguranca     = strtolower((string) env('SMTP_SECURE', 'tls'));
    $remetente     = env('SMTP_FROM');
    $nomeRemetente = env('SMTP_FROM_NAME', 'Sistema de Contatos');
    $destino       = env('CONTATO_DESTINO') ?? $remetente;

    // A porta SMTP NÃO é definida no código: ela deve ser informada em SMTP_PORT no .env.
    if ($host === null || $porta <= 0 || $usuario === null || $senha === null || $remetente === null || $destino === null) {
        error_log('[email] Configuração SMTP incompleta no .env (verifique SMTP_PORT, SMTP_USER, SMTP_PASS etc.).');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->Port       = $porta;
        $mail->SMTPAuth   = true;
        $mail->Username   = $usuario;
        $mail->Password   = $senha;
        $mail->SMTPSecure = ($seguranca === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout    = 15;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ],
        ];

        $mail->setFrom($remetente, $nomeRemetente);
        $mail->addAddress($destino);
        $mail->addReplyTo($contato['email'], $contato['nome']);

        $mail->isHTML(true);
        $mail->Subject = 'Novo contato pelo site: ' . $contato['nome'];
        $mail->Body    = montar_corpo_email($contato);
        $mail->AltBody = montar_texto_email($contato);

        $mail->send();

        return true;
    } catch (MailException $excecao) {
        error_log('[email] Falha ao enviar: ' . $mail->ErrorInfo);
        return false;
    } catch (Throwable $excecao) {
        error_log('[email] Erro inesperado: ' . $excecao->getMessage());
        return false;
    }
}

/** Corpo do e-mail em HTML (todos os dados são escapados). */
function montar_corpo_email(array $contato): string
{
    return '<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#1f2937; background:#f3f4f6; padding:20px;">
  <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:10px; padding:24px;">
    <h2 style="margin-top:0; color:#4f46e5;">Novo contato recebido</h2>
    <table style="width:100%; border-collapse:collapse;">
      <tr><td style="padding:8px 0; font-weight:bold; width:110px;">Nome:</td><td>' . e($contato['nome']) . '</td></tr>
      <tr><td style="padding:8px 0; font-weight:bold;">E-mail:</td><td>' . e($contato['email']) . '</td></tr>
      <tr><td style="padding:8px 0; font-weight:bold;">Telefone:</td><td>' . e($contato['telefone']) . '</td></tr>
    </table>
    <h3 style="margin-bottom:6px;">Mensagem</h3>
    <div style="background:#f9fafb; border-left:4px solid #4f46e5; padding:12px; white-space:pre-line;">' . e($contato['mensagem']) . '</div>
  </div>
</body>
</html>';
}

/** Versão em texto puro do e-mail (para clientes que não exibem HTML). */
function montar_texto_email(array $contato): string
{
    return "Novo contato recebido\n\n"
        . 'Nome: ' . $contato['nome'] . "\n"
        . 'E-mail: ' . $contato['email'] . "\n"
        . 'Telefone: ' . $contato['telefone'] . "\n\n"
        . "Mensagem:\n" . $contato['mensagem'] . "\n";
}
