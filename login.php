<?php
// ============================================================
// MINDGEST ERP — Página de Login
// Ficheiro: login.php (na raiz do projecto)
// ============================================================

// Iniciar sessão ANTES de qualquer output
session_start();

require_once 'includes/config.php';
require_once 'includes/auth.php';

// Se o utilizador já está autenticado, redirecionar para o dashboard
if (isset($_SESSION['utilizador_id'])) {
    redirecionar('/mindgest/index.php');
}

$erro  = '';
$email = '';

// ============================================================
// PROCESSAR O FORMULÁRIO DE LOGIN (POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validação básica dos campos
    if (empty($email) || empty($password)) {
        $erro = 'Por favor preencha o email e a password.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Formato de email inválido.';
    } else {
        // Tentar fazer login (função definida em auth.php)
        if (fazer_login($email, $password)) {

            // Login com sucesso!
            // Redirecionar para a página que o utilizador tentou aceder,
            // ou para o dashboard por omissão
            $destino = $_SESSION['url_pretendida'] ?? '/mindgest/index.php';
            unset($_SESSION['url_pretendida']); // Limpar após usar
            redirecionar($destino);

        } else {
            // Credenciais erradas
            $erro = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708"/>
</svg> Email ou password incorrectos. Tente novamente.';
            // Por segurança, não especificar qual dos dois está errado
        }
    }
}

// Verificar se acabou de fazer logout
$logout = isset($_GET['logout']) && $_GET['logout'] === '1';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindGest ERP - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="icon" type="png" href="assets/images/MG.ico">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --azul:    #0061b2;
            --verde:   #66d76e;
            --escuro:  #0f172a;
            --meioscuro: #1e293b;
            --cinza:   #334155;
            --texto:   #f1f5f9;
            --suave:   #94a3b8;
            --erro:    #ef4444;
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
        }

        body {
            background: var(--escuro);
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow-x: auto;
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


        /* ── PAINEL ESQUERDO (decorativo) ──────────────────────── */
        .painel-esquerdo {
            background: linear-gradient(145deg, #0061b2 0%, #004a8a 40%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px;
            position: relative;
            overflow: hidden;
        }

        /* Círculos decorativos de fundo */
        .painel-esquerdo::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(102, 215, 110, 0.08);
            top: -150px; right: -150px;
        }
        .painel-esquerdo::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255, 0.04);
            bottom: -80px; left: -80px;
        }

        /* Grade de pontos decorativa */
        .grade-pontos {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .logo-login {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 60px;
        }

        .logo-icone {
            width: 56px; height: 56px;
            display: flex;
            align-items: center;

        }

        .logo-nome {
            font-size: 26px;
            font-weight: 800;
            color: white;
            letter-spacing: -0.5px;
        }

        .slogan-titulo {
            position: relative;
            z-index: 1;
            font-size: 30px;
            font-weight: 800;
            color: white;
            line-height: 1.15;
            margin-bottom: 24px;
            letter-spacing: -1px;
        }

        .slogan-titulo span {
            color: var(--verde);
        }

        .slogan-desc {
            position: relative;
            z-index: 1;
            font-size: 15px;
            color: rgba(255,255,255,0.65);
            line-height: 1.7;
            max-width: 1000px;
            margin-bottom: 48px;
        }

        /* Chips de funcionalidades */
        .chips {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .chip {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.85);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 500;
            backdrop-filter: blur(6px);
        }

        /* ── PAINEL DIREITO (formulário) ────────────────────────── */
        .painel-direito {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: var(--meioscuro);
        }

        .caixa-login {
            width: 100%;
            max-width: 420px;
            animation: entrar 0.5s ease forwards;
        }

        @keyframes entrar {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .titulo-form {
            font-size: 40px;
            font-weight: 800;
            color: var(--texto);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .subtitulo-form {
            font-size: 20px;
            color: var(--suave);
            margin-bottom: 36px;
        }

        /* Alerta de logout */
        .alerta-logout {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Alerta de erro */
        .alerta-erro {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--erro);
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: sacudir 0.4s ease;
        }

        @keyframes sacudir {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-6px); }
            40%, 80% { transform: translateX(6px); }
        }

        /* Grupos de campos */
        .grupo {
            margin-bottom: 20px;
        }

        .grupo label {
            display: block;
            font-size: 18px;
            font-weight: 500;
            color: var(--suave);
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        /* Wrapper do campo com ícone */
        .campo-wrapper {
            position: relative;
        }

        .campo-icone {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            pointer-events: none;
            opacity: 0.5;
        }

        .campo-wrapper input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            background: var(--cinza);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: var(--texto);
            font-size: 14px;
            transition: all 0.2s;
            outline: none;
        }

        .campo-wrapper input:focus {
            border-color: var(--azul);
            background: rgba(51, 65, 85, 0.8);
            box-shadow: 0 0 0 3px rgba(0, 97, 178, 0.2);
        }

        .campo-wrapper input::placeholder {
            color: var(--suave);
            opacity: 0.6;
        }

        /* Botão de mostrar/esconder password */
        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--suave);
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--texto); }

        /* Link "esqueci a password" */
        .link-esqueceu {
            display: block;
            text-align: right;
            font-size: 12px;
            color: var(--suave);
            text-decoration: none;
            margin-top: 8px;
            transition: color 0.2s;
        }
        .link-esqueceu:hover { color: var(--azul); }

        /* Botão principal de login */
        .btn-login {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            background: linear-gradient(135deg, var(--azul), #0080e0);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 97, 178, 0.4);
        }
        .btn-login:hover::after { opacity: 1; }

        .btn-login:active { transform: translateY(0); }

        /* Estado de carregamento do botão */
        .btn-login.carregando {
            pointer-events: none;
            opacity: 0.7;
        }

        /* Separador */
        .separador {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--cinza);
            font-size: 15px;
        }
        .separador::before, .separador::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--cinza);
        }

        /* Credenciais de demo */
        .demo-creds {
            background: rgba(0, 97, 178, 0.08);
            border: 1px solid rgba(0, 97, 178, 0.2);
            border-radius: 10px;
            padding: 12px 14px;
        }

        .demo-titulo {
            font-size: 15px;
            font-weight: 600;
            color: var(--suave);
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }

        .demo-linha {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 12px;
            color: var(--suave);
            cursor: pointer;
            border-radius: 4px;
            transition: color 0.2s;
        }
        .demo-linha:hover { color: var(--texto); }

        .demo-perfil {
            font-weight: 600;
            color: var(--azul);
            font-size: 15px;
            background: rgba(0,97,178,0.15);
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Rodapé */
        .rodape-login {
            margin-top: 32px;
            text-align: center;
            font-size: 12px;
            color: var(--cinza);
        }

        /* Responsivo: mobile */
        @media (max-width: 768px) {
            body { grid-template-columns: 1fr; }
            .painel-esquerdo { display: none; }
            .painel-direito { padding: 24px; }
        }
    </style>
</head>
<body>

<!-- ════════════════════════════════════════════════
     PAINEL ESQUERDO — Visual / Marca
════════════════════════════════════════════════ -->
<div class="painel-esquerdo">
    <div class="grade-pontos"></div>

    <div class="logo-login">
        <div class="logo-icone">
        <div class="logo-icon"><img class="logo-icon" src="assets/images/MindGest.png"></div>
        </div>
    </div>

    <h1 class="slogan-titulo">
        Gerencie o seu negócio com <span>inteligência</span>
    </h1>

    <p class="slogan-desc">
        Sistema ERP completo para empresas, desde a faturação, inventário, recursos humanos e conformidade com a AGT numa única plataforma.
    </p>

    <div class="chips">
        <span class="chip">Facturação</span>
        <span class="chip">Gestão</span>
        <span class="chip">Contas</span>
        <span class="chip">Analytics</span>
        <span class="chip">IVA</span>
        <span class="chip">SAF-T</span>
    </div>
</div>


<!-- ════════════════════════════════════════════════
     PAINEL DIREITO — Formulário de Login
════════════════════════════════════════════════ -->
<div class="painel-direito">
    <div class="caixa-login">

        <h2 class="titulo-form" style="text-align:center">Bem-vindo!</h2>
        <p class="subtitulo-form">Introduza as suas credenciais.</p>

        <!-- Mensagem de logout bem-sucedido -->
        <?php if ($logout): ?>
            <div class="alerta-logout">
                ✓ &nbsp; Sessão terminada com sucesso. Até breve!
            </div>
        <?php endif; ?>

        <!-- Mensagem de erro -->
        <?php if ($erro): ?>
            <div class="alerta-erro">
                ✗ &nbsp; <?= sanitizar($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de login -->
        <form method="POST" action="" id="formLogin">

            <!-- Campo Email -->
            <div class="grupo">
                <label for="email">Email</label>
                <div class="campo-wrapper">
                    <span class="campo-icone"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-at" viewBox="0 0 16 16">
  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
  <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
</svg></span>
                    <input style="font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:15px"
                        type="email"
                        id="email"
                        name="email"
                        value="<?= sanitizar($email) ?>"
                        placeholder="utilizador@empresa.ao"
                        autocomplete="email"
                        required
                        autofocus>
                </div>
            </div>

            <!-- Campo Password -->
            <div class="grupo">
                <label for="password">Password</label>
                <div class="campo-wrapper">
                    <span class="campo-icone"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-key" viewBox="0 0 16 16">
  <path d="M0 8a4 4 0 0 1 7.465-2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L13 9.207l-.646.647a.5.5 0 0 1-.708 0L11 9.207l-.646.647a.5.5 0 0 1-.708 0L9 9.207l-.646.647A.5.5 0 0 1 8 10h-.535A4 4 0 0 1 0 8m4-3a3 3 0 1 0 2.712 4.285A.5.5 0 0 1 7.163 9h.63l.853-.854a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.793-.793-1-1h-6.63a.5.5 0 0 1-.451-.285A3 3 0 0 0 4 5"/>
  <path d="M4 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
</svg></span>
                    <input  style="font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:15px"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="********"
                        autocomplete="current-password"
                        required>
                </div>
                <a href="/mindgest/esqueci_password.php" class="link-esqueceu"  style="font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:15px">
                    Esqueceu a sua password?
                </a>
            </div>

            <!-- Botão de login -->
            <button type="submit" class="btn-login" id="btnLogin"  style="font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;">
                Entrar →
            </button>
        </form>

        <div class="separador">Credenciais de teste</div>

        <!-- Credenciais de demonstração (remover em produção!) -->
        <div class="demo-creds">
            <div class="demo-titulo"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                </svg> Clique para preencher automaticamente</div>

            <div class="demo-linha" onclick="preencherDemo('admin@mindgest.ao','Admin@2025')">
                <span>admin@mindgest.ao</span>
                <span class="demo-perfil">Admin</span>
            </div>
            <div class="demo-linha" onclick="preencherDemo('gestor@mindgest.ao','Gestor@2025')">
                <span>gestor@mindgest.ao</span>
                <span class="demo-perfil">Gestor</span>
            </div>
            <div class="demo-linha" onclick="preencherDemo('conta@mindgest.ao','Conta@2025')">
                <span>conta@mindgest.ao</span>
                <span class="demo-perfil">Contabilista</span>
            </div>
            <div class="demo-linha" onclick="preencherDemo('operador@mindgest.ao','Oper@2025')">
                <span>operador@mindgest.ao</span>
                <span class="demo-perfil">Operador</span>
            </div>
        </div>

        <div class="rodape-login">
            MindGest ERP v1.0 &nbsp;·&nbsp; Certificação AGT &nbsp;·&nbsp; AO Angola
        </div>
    </div>
</div>


<script>
// Mostrar/esconder password


// Preencher automaticamente com credenciais de demo
function preencherDemo(email, password) {
    document.getElementById('email').value    = email;
    document.getElementById('password').value = password;
    document.getElementById('password').type  = 'password';
}

// Mostrar estado de carregamento ao submeter
document.getElementById('formLogin').addEventListener('submit', function() {
    const btn = document.getElementById('btnLogin');
    btn.textContent = 'A entrar...';
    btn.classList.add('carregando');
});
</script>

</body>
</html>
