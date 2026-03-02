@echo off
cd /d "%~dp0"

where composer >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERRO] Composer nao encontrado!
    pause
    goto :EOF
)

echo [OK] Composer encontrado!
composer require mpdf/mpdf

if exist vendor\mpdf\mpdf (
    echo [SUCESSO] mPDF instalado com sucesso!
) else (
    echo [ERRO] Instalacao falhou. Verifique composer.json e permissões.
)

pause
