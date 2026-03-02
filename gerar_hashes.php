<?php
// MINDGEST — Gerador de Hashes para Passwords
// Execute este ficheiro no browser: http://localhost/mindgest/gerar_hashes.php

$passwords = [
    ["nome" => "Administrador",    "email" => "admin@mindgest.ao",    "password" => "Admin@2025",   "perfil" => "admin"],
    ["nome" => "Gestor Comercial",  "email" => "gestor@mindgest.ao",   "password" => "Gestor@2025",  "perfil" => "gestor"],
    ["nome" => "Operador",          "email" => "operador@mindgest.ao", "password" => "Oper@2025",    "perfil" => "operador"],
    ["nome" => "Contabilista",          "email" => "conta@mindgest.ao", "password" => "Conta@2025",    "perfil" => "contabilista"],
];

echo "<pre style=font-family:monospace;font-size:13px;background:#1e293b;color:#f1f5f9;padding:20px;border-radius:10px;>";
echo "-- Cole este SQL no phpMyAdmin:

";
echo "USE mindgest_db;
";
echo "DELETE FROM utilizadores;

";

foreach ($passwords as $u) {
    $hash = password_hash($u["password"], PASSWORD_BCRYPT);
    echo "INSERT INTO utilizadores (nome, email, password, perfil, estado) VALUES (
";
    echo "    " . json_encode($u["nome"]) . ",
";
    echo "    " . json_encode($u["email"]) . ",
";
    echo "    " . json_encode($hash) . ",
";
    echo "    " . json_encode($u["perfil"]) . ",
";
    echo "    " . json_encode("activo") . "
";
    echo ");

";
}

echo "-- Verifique as passwords geradas:
";
foreach ($passwords as $u) {
    echo $u["email"] . " → " . $u["password"] . "
";
}
echo "</pre>";
?>