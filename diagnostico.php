<?php
/**
 * Diagnóstico do Plugin SM Student Control
 *
 * Este arquivo pode ser usado para diagnosticar problemas com o plugin
 * sem carregar todo o WordPress.
 *
 * USO: Acesse diretamente via navegador ou execute via linha de comando
 */

// Prevenir acesso direto não autorizado
if (!defined('ABSPATH') && !isset($_SERVER['REQUEST_METHOD'])) {
    echo "Acesso direto não permitido\n";
    exit;
}

echo "=== DIAGNÓSTICO DO PLUGIN SM STUDENT CONTROL ===\n\n";

// Verificar se constantes estão definidas
echo "1. Verificando constantes...\n";
$constants = [
    'SM_STUDENT_CONTROL_VERSION',
    'SM_STUDENT_CONTROL_DIR',
    'SM_STUDENT_CONTROL_URL',
    'SM_STUDENT_CONTROL_BASENAME'
];

foreach ($constants as $const) {
    if (defined($const)) {
        echo "   ✅ {$const}: " . constant($const) . "\n";
    } else {
        echo "   ❌ {$const}: NÃO DEFINIDA\n";
    }
}

echo "\n2. Verificando arquivos principais...\n";
$files_to_check = [
    'includes/class-loader.php',
    'includes/class-settings.php',
    'includes/class-data.php',
    'includes/class-jwt.php',
    'includes/class-cache.php',
    'admin/class-admin.php',
    'frontend/class-shortcode-handler.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "   ✅ {$file}\n";
    } else {
        echo "   ❌ {$file} - ARQUIVO NÃO ENCONTRADO\n";
    }
}

echo "\n3. Verificando funções WordPress...\n";
$wp_functions = [
    'wp_get_current_user',
    'is_admin',
    'get_option',
    'add_action',
    'add_shortcode'
];

foreach ($wp_functions as $func) {
    if (function_exists($func)) {
        echo "   ✅ {$func}\n";
    } else {
        echo "   ⚠️  {$func} - NÃO DISPONÍVEL (WordPress não carregado)\n";
    }
}

echo "\n4. Verificando banco de dados...\n";
if (isset($GLOBALS['wpdb'])) {
    echo "   ✅ Conexão com banco de dados disponível\n";

    // Verificar tabelas do LMS
    $required_tables = [
        $GLOBALS['wpdb']->prefix . 'stm_lms_user_courses',
        $GLOBALS['wpdb']->prefix . 'stm_lms_courses'
    ];

    foreach ($required_tables as $table) {
        $exists = $GLOBALS['wpdb']->get_var("SHOW TABLES LIKE '$table'");
        if ($exists === $table) {
            echo "   ✅ Tabela {$table} existe\n";
        } else {
            echo "   ⚠️  Tabela {$table} NÃO ENCONTRADA\n";
        }
    }
} else {
    echo "   ⚠️  Conexão com banco de dados não disponível (WordPress não carregado)\n";
}

echo "\n5. Teste de carregamento das classes...\n";

// Teste seguro de carregamento
$classes_to_test = [
    'SM_Student_Control_Settings',
    'SM_Student_Control_Data',
    'SM_Student_Control_JWT'
];

foreach ($classes_to_test as $class) {
    if (class_exists($class)) {
        echo "   ✅ Classe {$class} carregada\n";
    } else {
        echo "   ❌ Classe {$class} NÃO CARREGADA\n";
    }
}

echo "\n=== DIAGNÓSTICO CONCLUÍDO ===\n";

if (defined('ABSPATH')) {
    echo "\nExecutado dentro do WordPress\n";
} else {
    echo "\nExecutado fora do WordPress (diagnóstico limitado)\n";
}

echo "\nSe você está vendo este diagnóstico, o plugin não está causando erro fatal.\n";
echo "Verifique os logs do WordPress em wp-content/debug.log para mais detalhes.\n";