<?php
// ============================================================
// MINDGEST ERP — Sem Permissão
// Ficheiro: sem_permissao.php
// Mostrado quando o utilizador não tem o perfil necessário.
// ============================================================

session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

requer_login(); // Tem de estar autenticado para ver esta página

$util = utilizador_actual();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MindGest ERP - Sem Perminssão</title>
    <link rel="icon" type="png" href="assets/images/MG.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
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

        .caixa {
            text-align: center;
            max-width: 420px;
            animation: entrar .4s ease;
        }
        @keyframes entrar {
            from { opacity:0; transform:translateY(20px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .icone { font-size: 80px; margin-bottom: 24px; }
        .titulo { font-family:Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:32px; font-weight:800; margin-bottom:12px; color:#f1f5f9; }
        .desc { font-size:15px; color:#94a3b8; line-height:1.7; margin-bottom:32px; }
        .btn {
            display:inline-block; padding:13px 28px;
            background:linear-gradient(135deg,#0061b2,#0080e0);
            color:white; text-decoration:none;
            border-radius:10px; font-family:'Syne',sans-serif;
            font-weight:700; font-size:14px;
            transition:all .25s; margin:6px;
        }
        .btn:hover { transform:translateY(-2px); box-shadow:0 10px 25px rgba(0,97,178,.35); }
        .btn-sec {
            background: #334155;
        }
        .info {
            margin-top:24px; padding:14px 18px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:10px; font-size:13px; color:#64748b;
            font-size:15px
        }
    </style>
</head>
<body>
<div class="caixa">
    <div class="icone"><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-shield-lock" viewBox="0 0 16 16">
  <path d="M5.338 1.59a61 61 0 0 0-2.837.856.48.48 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533q.18.085.293.118a1 1 0 0 0 .101.025 1 1 0 0 0 .1-.025q.114-.034.294-.118c.24-.113.547-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7 7 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7 7 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56"/>
  <path d="M9.5 6.5a1.5 1.5 0 0 1-1 1.415l.385 1.99a.5.5 0 0 1-.491.595h-.788a.5.5 0 0 1-.49-.595l.384-1.99a1.5 1.5 0 1 1 2-1.415"/>
</svg></div>
    <h1 class="titulo">Acesso Negado</h1>
    <p class="desc" style="font-size:18px">
        Não tem permissão para aceder a esta página.
        O seu perfil de <strong><?= sanitizar($util['perfil'] ?? '') ?></strong>
        não tem acesso a esta área do sistema.
    </p>
    <a href="/mindgest/index.php" class="btn" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:20px"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
  <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/>
</svg> Ir para o Dashboard</a>
    <a href="/mindgest/logout.php" class="btn btn-sec" style="font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif; font-size:20px"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
  <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
</svg> Mudar de Conta</a>
    <div class="info">
        Se precisar de acesso, contacte o administrador do sistema.
    </div>
</div>
</body>
</html>
