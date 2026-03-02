<?php
// ============================================================
// MINDGEST ERP — Logout
// Ficheiro: logout.php (na raiz do projecto)
// Termina a sessão do utilizador e redireciona para o login.
// ============================================================

session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// A função fazer_logout() limpa a sessão e redireciona
fazer_logout();
