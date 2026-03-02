<?php
// ============================================================
// MINDGEST ERP — Dashboard Principal
// Ficheiro: index.php (raiz do projecto)
// ============================================================

require_once 'includes/config.php'; // Carrega configurações e BD
require_once 'includes/auth.php';   // Carrega sistema de login
requer_login();                        // Redireciona se não autenticado

// Variáveis para o layout
$pagina_actual  = 'dashboard';
$titulo_pagina  = 'Painel Principal';

// Ligar à base de dados
$bd = ligar_bd();

// ---- Buscar estatísticas para os cartões do dashboard ----

// Total de clientes activos
$stmt = $bd->query("SELECT COUNT(*) AS total FROM clientes WHERE estado = 'activo'");
$total_clientes = $stmt->fetchColumn();

// Total de produtos
$stmt = $bd->query("SELECT COUNT(*) AS total FROM produtos WHERE estado = 'activo'");
$total_produtos = $stmt->fetchColumn();

// Faturas emitidas este mês
$stmt = $bd->query("
    SELECT COUNT(*) AS total, COALESCE(SUM(total), 0) AS valor
    FROM faturas
    WHERE MONTH(data_fatura) = MONTH(CURDATE())
      AND YEAR(data_fatura)  = YEAR(CURDATE())
      AND estado != 'anulada'
");
$fat_mes = $stmt->fetch();
$faturas_mes     = $fat_mes['total'];
$faturacao_mes   = $fat_mes['valor'];

// Produtos com stock baixo
$stmt = $bd->query("SELECT COUNT(*) FROM produtos WHERE stock <= stock_minimo AND estado = 'activo'");
$stock_baixo = $stmt->fetchColumn();

// Últimas 5 faturas
$stmt = $bd->query("
    SELECT f.numero, f.data_fatura, f.total, f.estado, c.nome AS cliente
    FROM faturas f
    JOIN clientes c ON c.id = f.cliente_id
    ORDER BY f.criado_em DESC
    LIMIT 5
");
$ultimas_faturas = $stmt->fetchAll();

// Incluir o topo do layout (sidebar + abertura do <main>)
include 'includes/layout.php';
?>

<!-- ====== CONTEÚDO DA PÁGINA DASHBOARD ====== -->

<style>
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
</style>

<!-- Barra superior com título -->
<div class="top-bar">
    <div>
        <h1 class="page-title">Dashboard Executivo</h1>
        <p class="page-subtitle">Visão geral do negócio → <?= date('d.m.Y - h:i:s A') ?></p>
    </div>
</div>

<!-- Alerta de conformidade AGT -->
<div class="alert alert-success">
    <span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
  <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
  <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
</svg></span>
    <span><strong>Sistema Certificado AGT:</strong> MindGest ERP está preparado para
    emissão de SAF-T e facturação electrónica conforme a legislação angolana.</span>
</div>

<!-- ---- Cartões de Estatísticas ---- -->
<div class="stats-grid">

    <!-- Faturação do mês -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Facturação do Mês</div>
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8m5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0"/>
  <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195z"/>
  <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083q.088-.517.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1z"/>
  <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 6 6 0 0 1 3.13-1.567"/>
</svg></div>
        </div>
        <!-- formatar_moeda() definida em config.php -->
        <div class="stat-value"><?= formatar_moeda($faturacao_mes) ?></div>
        <div class="stat-change positive">
            <?= $faturas_mes ?> factura(s) emitida(s)
        </div>
    </div>

    <!-- Clientes Activos -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Clientes Activos</div>
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
  <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
  <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
</svg></div>
        </div>
        <div class="stat-value"><?= $total_clientes ?></div>
        <div class="stat-change positive">Cliente(s) registado(s)</div>
    </div>

    <!-- Produtos em Stock -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">Produtos Activos</div>
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box" viewBox="0 0 16 16">
  <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5 8 5.961 14.154 3.5zM15 4.239l-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464z"/>
</svg></div>
        </div>
        <div class="stat-value"><?= $total_produtos ?></div>
        <?php if ($stock_baixo > 0): ?>
            <!-- Mostrar alerta se houver stock baixo -->
            <div class="stat-change negative"><?= $stock_baixo ?> Produto(s) com stock baixo</div>
        <?php else: ?>
            <div class="stat-change positive">Stock OK</div>
        <?php endif; ?>
    </div>

    <!-- SAF-T Mensal -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-label">SAF-T Mensal</div>
            <div class="stat-icon"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-file-earmark-code" viewBox="0 0 16 16">
  <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
  <path d="M8.646 6.646a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L10.293 9 8.646 7.354a.5.5 0 0 1 0-.708m-1.292 0a.5.5 0 0 0-.708 0l-2 2a.5.5 0 0 0 0 .708l2 2a.5.5 0 0 0 .708-.708L5.707 9l1.647-1.646a.5.5 0 0 0 0-.708"/>
</svg></div>
        </div>
        <div class="stat-value">Pendente</div>
        <div class="stat-change warning"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
  <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9z"/>
  <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1zm1.038 3.018a6 6 0 0 1 .924 0 6 6 0 1 1-.924 0M0 3.5c0 .753.333 1.429.86 1.887A8.04 8.04 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5M13.5 1c-.753 0-1.429.333-1.887.86a8.04 8.04 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1"/>
</svg> Enviar até dia 20</div>
    </div>
</div>

<!-- ---- Tabela de Últimas Faturas ---- -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
  <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z"/>
  <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z"/>
  <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5"/>
</svg> Últimas Facturas Emitidas</h2>
        <a href="/mindgest/pages/faturas.php" class="btn btn-secondary btn-small" style="text-decoration:none">
            Ver todas →
        </a>
    </div>

    <?php if (empty($ultimas_faturas)): ?>
        <!-- Estado vazio: ainda não há faturas -->
        <div class="empty-state">
            <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" fill="currentColor" class="bi bi-receipt" viewBox="0 0 16 16">
  <path d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z"/>
  <path d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5"/>
</svg></div>
            <p>Ainda não foram emitidas faturas</p>
        </div>

    <?php else: ?>
        <!-- Tabela com as últimas faturas -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nº Factura</th>
                        <th>Data</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas_faturas as $fat): ?>
                    <tr>
                        <td><strong><?= sanitizar($fat['numero']) ?></strong></td>
                        <!-- Formatar data de AAAA-MM-DD para DD/MM/AAAA -->
                        <td><?= date('d/m/Y', strtotime($fat['data_fatura'])) ?></td>
                        <td><?= sanitizar($fat['cliente']) ?></td>
                        <td><?= formatar_moeda($fat['total']) ?></td>
                        <td>
                            <?php
                            // Mostrar badge com cor conforme o estado
                            $badge = match($fat['estado']) {
                                'paga'    => '<span class="badge badge-success">✓ Paga</span>',
                                'anulada' => '<span class="badge badge-danger">✗ Anulada</span>',
                                default   => '<span class="badge badge-info">● Emitida</span>',
                            };
                            echo $badge;
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Fechar a ligação não é necessário com PDO (PHP faz isso automaticamente),
// mas pode-se fazer explicitamente:
$bd = null;

// Incluir o rodapé (fecha </main>, </body>, </html>)
include 'includes/footer.php';
?>
