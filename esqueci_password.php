<?php
// ============================================================
// MINDGEST ERP — Recuperação de Password
// Ficheiro: esqueci_password.php
// ============================================================

session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Se já está autenticado, redirecionar
if (isset($_SESSION['utilizador_id'])) {
    redirecionar('/mindgest/index.php');
}

$mensagem = '';
$tipo_msg = '';
$passo    = 'email'; // 'email' → pedir email | 'token' → inserir token + nova password

// ============================================================
// PASSO 1: Utilizador submete o email
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['passo'] ?? '') === 'email') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'Formato de email inválido.';
        $tipo_msg = 'erro';
    } else {
        $bd   = ligar_bd();
        $stmt = $bd->prepare(
            "SELECT id FROM utilizadores WHERE email = ? AND estado = 'activo'"
        );
        $stmt->execute([$email]);
        $utilizador = $stmt->fetch();

        // Por segurança, mostrar sempre a mesma mensagem
        // (não revelar se o email existe ou não)
        if ($utilizador) {
            // Gerar token único com expiração de 1 hora
            $token    = bin2hex(random_bytes(32)); // 64 caracteres aleatórios
            $expira   = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt2 = $bd->prepare(
                "UPDATE utilizadores
                 SET token_reset=?, token_expira_em=?
                 WHERE id=?"
            );
            $stmt2->execute([$token, $expira, $utilizador['id']]);

            // Em produção, enviar por email com PHPMailer.
            // Aqui mostramos o link directamente (para desenvolvimento):
            $link_reset = 'http://localhost/mindgest/esqueci_password.php'
                        . '?token=' . $token . '&email=' . urlencode($email);

            // Guardar na sessão para mostrar no passo 2
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_link']  = $link_reset; // Apenas para dev
        }

        $mensagem = 'Se o email existir no sistema, receberá instruções de recuperação em breve.';
        $tipo_msg = 'sucesso';
        $passo    = 'confirmacao';
    }
}

// ============================================================
// PASSO 2: Utilizador clica no link do email (com token)
// ============================================================
if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    $bd   = ligar_bd();
    $stmt = $bd->prepare(
        "SELECT id FROM utilizadores
         WHERE email=? AND token_reset=? AND token_expira_em > NOW()"
    );
    $stmt->execute([$email, $token]);
    $utilizador = $stmt->fetch();

    if ($utilizador) {
        $passo = 'nova_password';
        // Guardar na sessão para o passo 3
        $_SESSION['reset_id']    = $utilizador['id'];
        $_SESSION['reset_token'] = $token;
    } else {
        $mensagem = 'Link inválido ou expirado. Solicite um novo reset de password.';
        $tipo_msg = 'erro';
    }
}

// ============================================================
// PASSO 3: Utilizador define a nova password
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['passo'] ?? '') === 'nova_password') {
    $nova_pass    = $_POST['nova_password']    ?? '';
    $confirmar    = $_POST['confirmar_password'] ?? '';
    $id_reset     = $_SESSION['reset_id']    ?? 0;
    $token_reset  = $_SESSION['reset_token'] ?? '';

    if (strlen($nova_pass) < 8) {
        $mensagem = 'A password deve ter pelo menos 8 caracteres.';
        $tipo_msg = 'erro';
        $passo    = 'nova_password';
    } elseif ($nova_pass !== $confirmar) {
        $mensagem = 'As passwords não coincidem.';
        $tipo_msg = 'erro';
        $passo    = 'nova_password';
    } elseif (!$id_reset) {
        redirecionar('/mindgest/esqueci_password.php');
    } else {
        $bd   = ligar_bd();
        $hash = password_hash($nova_pass, PASSWORD_BCRYPT);

        // Actualizar password e limpar o token
        $stmt = $bd->prepare(
            "UPDATE utilizadores
             SET password=?, token_reset=NULL, token_expira_em=NULL
             WHERE id=? AND token_reset=?"
        );
        $stmt->execute([$hash, $id_reset, $token_reset]);

        // Limpar sessão de reset
        unset($_SESSION['reset_id'], $_SESSION['reset_token']);

        $mensagem = 'Password alterada com sucesso! Já pode fazer login.';
        $tipo_msg = 'sucesso';
        $passo    = 'concluido';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindGest ERP - Recuperar Password</title>
    <link rel="icon" type="png" href="assets/images/MG.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --azul: #0061b2; --verde: #10b981; --escuro: #0f172a;
            --meioscuro: #1e293b; --cinza: #334155;
            --texto: #f1f5f9; --suave: #94a3b8; --erro: #ef4444;
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif
        }

            body {
    overflow-y: auto;
    }
    body::-webkit-scrollbar {
    width: 6px;
    }

    body::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
    }

    body::-webkit-scrollbar-thumb {
    background: var(--accent-primary);
    border-radius: 3px;
    }

    .modal-content {
    overflow-y:auto;
    }
    .modal-content::-webkit-scrollbar {
    width: 6px;
    }

    .modal-content::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
    }

    .modal-content::-webkit-scrollbar-thumb {
    background: var(--accent-primary);
    border-radius: 3px;
    }

        body {
            background: var(--escuro);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .caixa {
            background: var(--meioscuro);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 440px;
            animation: entrar 0.4s ease;
        }
        @keyframes entrar {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .logo-mini {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }
        .logo-mini span { font-family: 'Syne', sans-serif; font-size: 20px; font-weight: 800; color: var(--texto); }
        .titulo { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; color: var(--texto); margin-bottom: 8px; }
        .desc { font-size: 14px; color: var(--suave); margin-bottom: 28px; line-height: 1.6; }
        .alerta {
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .alerta.sucesso { background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.3); color: var(--verde); }
        .alerta.erro    { background:rgba(239,68,68,.1);  border:1px solid rgba(239,68,68,.3);  color: var(--erro);  }
        .grupo { margin-bottom: 18px; }
        .grupo label { display:block; font-size:12px; font-weight:500; color:var(--suave); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
        .campo { width:100%; padding:13px 16px; background:var(--cinza); border:1px solid rgba(255,255,255,.08); border-radius:10px; color:var(--texto); font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color .2s; }
        .campo:focus { border-color:var(--azul); box-shadow:0 0 0 3px rgba(0,97,178,.2); }
        .btn { width:100%; padding:14px; background:linear-gradient(135deg,var(--azul),#0080e0); border:none; border-radius:10px; color:white; font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:all .25s; }
        .btn:hover { transform:translateY(-2px); box-shadow:0 10px 25px rgba(0,97,178,.35); }
        .link-voltar { display:block; text-align:center; margin-top:20px; font-size:13px; color:var(--suave); text-decoration:none; transition:color .2s; }
        .link-voltar:hover { color:var(--texto); }
        .caixa-info { background:rgba(0,97,178,.08); border:1px solid rgba(0,97,178,.2); border-radius:10px; padding:16px; margin-bottom:20px; font-size:13px; color:var(--suave); line-height:1.7; }
        .caixa-info code { background:var(--cinza); padding:2px 8px; border-radius:4px; color:#60a5fa; font-size:12px; word-break:break-all; }
        .icone-grande { font-size:56px; text-align:center; margin-bottom:16px; }
    </style>
</head>
<body>
<div class="caixa">
    <?php if ($mensagem): ?>
        <div class="alerta <?= $tipo_msg === 'sucesso' ? 'sucesso' : 'erro' ?>">
            <?= $tipo_msg === 'sucesso' ? '✓' : '✕' ?> &nbsp;
            <?= sanitizar($mensagem) ?>
        </div>
    <?php endif; ?>

    <!-- ── PASSO 1: Pedir email ── -->
    <?php if ($passo === 'email'): ?>
        <h2 class="titulo" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:35px;text-align:center">Recuperar Password</h2>
        <p class="desc">
            Introduza o seu email e receberá um link para redefinir a password.
        </p>
        <form method="POST">
            <input type="hidden" name="passo" value="email">
            <div class="grupo">
                <label>Email registado</label>
                <input class="campo" type="email" name="email" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;"
                       placeholder="utilizador@empresa.ao" required autofocus>
            </div>
            <button type="submit" class="btn"  style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:20px"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
  <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
</svg> Enviar Link de Recuperação</button>
        </form>

    <!-- ── PASSO 1b: Confirmação de envio ── -->
    <?php elseif ($passo === 'confirmacao'): ?>
        <div class="icone-grande"><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
  <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
</svg></div>
        <h2 class="titulo" style="text-align:center; font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:35px">Email enviado!</h2>
        <p class="desc" style="text-align:center;">
            Verifique a sua caixa de correio e clique no link de recuperação.
            O link expira em <strong>1 hora</strong>.
        </p>

        <?php
        // Em desenvolvimento: mostrar o link directamente
        if (isset($_SESSION['reset_link'])): ?>
            <div class="caixa-info">
                <strong style="color:var(--texto);">Modo de Desenvolvimento</strong><br>
                Em produção, este link seria enviado por email. Para testar agora:<br><br>
                <code><?= sanitizar($_SESSION['reset_link']) ?></code>
            </div>
        <?php
        unset($_SESSION['reset_link']);
        endif; ?>

    <!-- ── PASSO 2: Definir nova password ── -->
    <?php elseif ($passo === 'nova_password'): ?>
        <h2 class="titulo" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:35px">Nova Password</h2>
        <p class="desc">Escolha uma password segura com pelo menos 8 caracteres.</p>
        <form method="POST">
            <input type="hidden" name="passo" value="nova_password">
            <div class="grupo">
                <label>Nova Password</label>
                <input class="campo" type="password" name="nova_password" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;"
                       placeholder="Mínimo 8 caracteres" required minlength="8">
            </div>
            <div class="grupo">
                <label>Confirmar Password</label>
                <input class="campo" type="password" name="confirmar_password" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;"
                       placeholder="Repita a password" required minlength="8">
            </div>
            <button type="submit" class="btn"  style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;">Definir Nova Password</button>
        </form>

    <!-- ── PASSO 3: Concluído ── -->
    <?php elseif ($passo === 'concluido'): ?>
        <div class="icone-grande"></div>
        <h2 class="titulo" style="text-align:center;">Password alterada!</h2>
        <p class="desc" style="text-align:center;">
            A sua password foi redefinida com sucesso. Pode fazer login agora.
        </p>
        <a href="/mindgest/login.php" class="btn" style="display:block;text-align:center;text-decoration:none;">
            → Ir para o Login
        </a>
    <?php endif; ?>

    <a href="/mindgest/login.php" class="link-voltar">← Voltar ao login</a>
</div>
</body>
</html>
