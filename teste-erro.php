<?php
/**
 * Teste Rápido - Encontrar o erro exato
 */

// Carregar WordPress
require_once('../../../../wp-load.php');

echo "<pre>";
echo "=== TESTE DO PLUGIN SM STUDENT CONTROL ===\n\n";

// 1. Testar inclusão de arquivos
echo "1. Testando inclusão de arquivos...\n";

$base_dir = __DIR__;
$files_to_test = [
    'includes/class-loader.php',
    'includes/class-settings.php',
    'includes/class-jwt.php',
    'frontend/class-shortcode-handler.php',
];

foreach ($files_to_test as $file) {
    $full_path = $base_dir . '/' . $file;
    if (!file_exists($full_path)) {
        echo "❌ ARQUIVO NÃO ENCONTRADO: {$file}\n";
    } else {
        echo "✅ {$file}\n";
    }
}

echo "\n2. Testando carregamento manual...\n";

// Definir constantes
define('SM_STUDENT_CONTROL_VERSION', '2.0.0');
define('SM_STUDENT_CONTROL_DIR', $base_dir . '/');
define('SM_STUDENT_CONTROL_URL', plugins_url() . '/sm-student-control/');
define('SM_STUDENT_CONTROL_BASENAME', 'sm-student-control/sm-student-control.php');

try {
    require_once $base_dir . '/includes/class-settings.php';
    echo "✅ SM_Student_Control_Settings carregada\n";
    
    require_once $base_dir . '/includes/class-loader.php';
    echo "✅ SM_Student_Control_Loader carregada\n";
    
    require_once $base_dir . '/frontend/class-shortcode-handler.php';
    echo "✅ SM_Student_Control_Shortcode_Handler carregada\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

echo "\n3. Verificando se shortcode está registrado...\n";
global $shortcode_tags;
if (isset($shortcode_tags['painel_professor'])) {
    echo "✅ Shortcode [painel_professor] registrado\n";
} else {
    echo "❌ Shortcode [painel_professor] NÃO registrado\n";
}

echo "\n4. Testando render do shortcode...\n";
try {
    $output = do_shortcode('[painel_professor]');
    echo "Saída do shortcode:\n";
    echo substr($output, 0, 500) . (strlen($output) > 500 ? '...' : '') . "\n";
} catch (Exception $e) {
    echo "❌ ERRO ao renderizar: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
echo "</pre>";
?>
