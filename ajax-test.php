<?php
/**
 * Teste AJAX Detalhado
 */

// Carregar WordPress
require_once('../../../../wp-load.php');

echo "<!DOCTYPE html><html><head><title>Teste AJAX</title><style>";
echo "body { font-family: monospace; background: #f5f5f5; padding: 20px; }";
echo ".success { color: green; font-weight: bold; }";
echo ".error { color: red; background: #ffe0e0; padding: 10px; margin: 10px 0; }";
echo ".info { color: #333; background: #e0f0ff; padding: 10px; margin: 10px 0; }";
echo "pre { background: white; padding: 15px; overflow-x: auto; max-height: 300px; }";
echo ".section { margin: 20px 0; border: 1px solid #ccc; padding: 15px; background: white; }";
echo "</style></head><body>";

echo "<h1>🔍 Teste de AJAX - Load Students</h1>";

// ====== 1. SIMULAR CHAMADA AJAX ======
echo "<div class='section'>";
echo "<h2>1. Simular Chamada AJAX</h2>";

// Criar nonce
$nonce = wp_create_nonce('sm_sc_frontend_nonce');
echo "<p class='info'>Nonce criado: <strong>" . substr($nonce, 0, 20) . "...</strong></p>";

// Preparar dados como se o JavaScript enviasse
$_POST = [
    'action' => 'sm_sc_load_students',
    'nonce' => $nonce,
    'search' => '',
    'course_id' => '',
    'page' => '1',
    'per_page' => '20'
];

echo "<p class='info'>POST data preparado:</p>";
echo "<pre>" . json_encode($_POST, JSON_PRETTY_PRINT) . "</pre>";

// ====== 2. VERIFICAR NONCE ======
echo "</div><div class='section'>";
echo "<h2>2. Verificar Nonce</h2>";

$nonce_valid = wp_verify_nonce($nonce, 'sm_sc_frontend_nonce');

if ($nonce_valid === 1) {
    echo "<p class='success'>✅ Nonce válido (resultado: 1)</p>";
} else if ($nonce_valid === 2) {
    echo "<p class='info'>⚠️ Nonce válido mas de uma ação diferente (resultado: 2)</p>";
} else {
    echo "<p class='error'>❌ Nonce INVÁLIDO (resultado: " . $nonce_valid . ")</p>";
}

// ====== 3. VERIFICAR USUÁRIO ======
echo "</div><div class='section'>";
echo "<h2>3. Verificar Usuário</h2>";

if (is_user_logged_in()) {
    $user = wp_get_current_user();
    echo "<p class='success'>✅ Usuário logado: {$user->user_login}</p>";
} else {
    echo "<p class='error'>❌ Nenhum usuário logado!</p>";
}

// ====== 4. TESTAR FUNÇÃO AJAX ======
echo "</div><div class='section'>";
echo "<h2>4. Testar Função AJAX</h2>";

// Simular o que aconteceria na função ajax_load_students
try {
    // Verificar professor data
    if (class_exists('SM_Student_Control_JWT')) {
        $professor_data = SM_Student_Control_JWT::get_professor_data();
        if ($professor_data) {
            echo "<p class='success'>✅ Dados do professor obtidos</p>";
            echo "<pre>" . json_encode($professor_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

            // Tentar obter estudantes
            if (class_exists('SM_Student_Control_Professor_Students')) {
                $args = [
                    'search' => '',
                    'course_id' => 0,
                    'page' => 1,
                    'per_page' => 20,
                    'offset' => 0,
                    'limit' => 20,
                ];

                echo "<p class='info'>Chamando get_professor_students com args:</p>";
                echo "<pre>" . json_encode($args, JSON_PRETTY_PRINT) . "</pre>";

                $students = SM_Student_Control_Professor_Students::get_professor_students(
                    $professor_data['school_id'],
                    $professor_data['professor_id'],
                    $args
                );

                echo "<p class='success'>✅ Estudantes obtidos: " . count($students) . " total</p>";
                
                if (!empty($students)) {
                    echo "<p>Primeiros 2 registros:</p>";
                    echo "<pre>" . json_encode(array_slice($students, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                }

                // Contar total
                if (method_exists('SM_Student_Control_Professor_Students', 'count_professor_students')) {
                    $total = SM_Student_Control_Professor_Students::count_professor_students(
                        $professor_data['school_id'],
                        $professor_data['professor_id']
                    );
                    echo "<p class='success'>✅ Total de estudantes: $total</p>";
                } else {
                    echo "<p class='error'>❌ Método count_professor_students não encontrado</p>";
                }
            } else {
                echo "<p class='error'>❌ Classe SM_Student_Control_Professor_Students não encontrada</p>";
            }
        } else {
            echo "<p class='error'>❌ Dados do professor não encontrados</p>";
        }
    } else {
        echo "<p class='error'>❌ Classe SM_Student_Control_JWT não encontrada</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

// ====== 5. RECOMENDAÇÕES ======
echo "<div class='section'>";
echo "<h2>5. Recomendações</h2>";
echo "<ul>";
echo "<li>Se o nonce é inválido, adicione 'force_refresh_session' ao teste</li>";
echo "<li>Se professor_data é nulo, verifique autenticação JWT</li>";
echo "<li>Se estudantes está vazio, verifique se há dados no banco</li>";
echo "</ul>";
echo "</div>";

echo "</body></html>";
?>
