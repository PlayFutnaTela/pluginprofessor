<?php
/**
 * Script para Remover Console Logs
 *
 * Execute este script para remover todos os console logs do plugin
 * após a fase de testes.
 *
 * USO: php remove-console-logs.php
 */

if (!defined('ABSPATH')) {
    // Executar fora do WordPress
    echo "🧹 Iniciando remoção de console logs do SM Student Control...\n\n";

    $plugin_dir = __DIR__;

    // Remover logs PHP (error_log)
    echo "📝 Removendo logs PHP (error_log)...\n";
    $php_files = glob($plugin_dir . '/**/*.php');
    $php_logs_removed = 0;

    foreach ($php_files as $file) {
        $content = file_get_contents($file);
        $original_content = $content;

        // Remover linhas com error_log que contenham [SM-SC
        $content = preg_replace('/^\s*error_log\(.*\[SM-SC.*\);\s*$/m', '', $content);

        if ($content !== $original_content) {
            file_put_contents($file, $content);
            $php_logs_removed++;
            echo "  ✅ " . str_replace($plugin_dir . '/', '', $file) . "\n";
        }
    }

    echo "📊 Total de arquivos PHP processados: " . count($php_files) . "\n";
    echo "🗑️ Logs PHP removidos: $php_logs_removed\n\n";

    // Remover logs JavaScript (console.log)
    echo "🌐 Removendo logs JavaScript (console.log)...\n";
    $js_files = glob($plugin_dir . '/**/*.js');
    $js_logs_removed = 0;

    foreach ($js_files as $file) {
        $content = file_get_contents($file);
        $original_content = $content;

        // Remover linhas com console.log que contenham [SM-SC
        $content = preg_replace('/^\s*console\.log\(.*\[SM-SC.*\);\s*$/m', '', $content);

        if ($content !== $original_content) {
            file_put_contents($file, $content);
            $js_logs_removed++;
            echo "  ✅ " . str_replace($plugin_dir . '/', '', $file) . "\n";
        }
    }

    echo "📊 Total de arquivos JS processados: " . count($js_files) . "\n";
    echo "🗑️ Logs JavaScript removidos: $js_logs_removed\n\n";

    // Remover arquivos de log
    echo "🗂️ Removendo arquivos de documentação de logs...\n";
    $log_files = [
        $plugin_dir . '/CONSOLE-LOGS.md',
        $plugin_dir . '/remove-console-logs.php'
    ];

    foreach ($log_files as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo "  ✅ " . basename($file) . " removido\n";
        }
    }

    echo "\n🎉 Remoção de console logs concluída!\n";
    echo "📋 Resumo:\n";
    echo "  - PHP logs removidos: $php_logs_removed\n";
    echo "  - JavaScript logs removidos: $js_logs_removed\n";
    echo "  - Arquivos de documentação removidos: " . count($log_files) . "\n\n";

    echo "⚠️ IMPORTANTE: Faça backup dos arquivos antes de executar este script!\n";
    echo "🔄 Para restaurar os logs, será necessário refazer as modificações manuais.\n";

} else {
    // Se executado dentro do WordPress, mostrar erro
    wp_die('Este script deve ser executado via linha de comando, não através do WordPress.');
}