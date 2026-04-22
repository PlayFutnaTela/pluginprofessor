<?php
/**
 * Diagnóstico Completo - Tabela de Estudantes não aparece
 * Verifica: PHP → AJAX → JavaScript → HTML
 */

// Carregar WordPress
require_once('../../../../wp-load.php');

echo "<!DOCTYPE html><html><head><title>Debug Tabela</title><style>";
echo "body { font-family: monospace; background: #f5f5f5; padding: 20px; }";
echo ".success { color: green; }";
echo ".error { color: red; background: #ffe0e0; padding: 10px; margin: 10px 0; }";
echo ".info { color: #333; background: #e0f0ff; padding: 10px; margin: 10px 0; }";
echo "pre { background: white; padding: 15px; overflow-x: auto; }";
echo "</style></head><body>";

echo "<h2>🔍 DIAGNÓSTICO - TABELA DE ESTUDANTES</h2>";

// ====== 1. VERIFICAR PLUGIN CARREGADO ======
echo "<h3>1️⃣ Plugin Carregado?</h3>";
if (class_exists('SM_Student_Control_Loader')) {
    echo "<p class='success'>✅ SM_Student_Control_Loader carregada</p>";
} else {
    echo "<p class='error'>❌ SM_Student_Control_Loader NÃO carregada!</p>";
}

// ====== 2. VERIFICAR SHORTCODE REGISTRADO ======
echo "<h3>2️⃣ Shortcode Registrado?</h3>";
global $shortcode_tags;
if (isset($shortcode_tags['painel_professor'])) {
    echo "<p class='success'>✅ Shortcode [painel_professor] registrado</p>";
} else {
    echo "<p class='error'>❌ Shortcode NÃO registrado!</p>";
}

// ====== 3. VERIFICAR USUÁRIO LOGADO ======
echo "<h3>3️⃣ Usuário Logado?</h3>";
$user = wp_get_current_user();
if ($user->ID) {
    echo "<p class='success'>✅ Usuário logado: {$user->user_login} (ID: {$user->ID})</p>";
} else {
    echo "<p class='error'>❌ Nenhum usuário logado!</p>";
}

// ====== 4. TESTAR AJAX ENDPOINT ======
echo "<h3>4️⃣ Testar AJAX Endpoint</h3>";
echo "<p>Testando: /wp-admin/admin-ajax.php?action=sm_sc_load_students</p>";

// Simular requisição AJAX
if (function_exists('wp_remote_post')) {
    $nonce = wp_create_nonce('sm_sc_nonce');
    
    $response = wp_remote_post(
        admin_url('admin-ajax.php'),
        [
            'method'  => 'POST',
            'body'    => [
                'action'    => 'sm_sc_load_students',
                'nonce'     => $nonce,
                'search'    => '',
                'course_id' => '',
                'page'      => '1',
                'per_page'  => '20'
            ],
            'blocking' => true,
        ]
    );
    
    if (is_wp_error($response)) {
        echo "<p class='error'>❌ Erro na requisição: " . $response->get_error_message() . "</p>";
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        echo "<p class='info'>Status: <strong>" . $status_code . "</strong></p>";
        
        if ($status_code === 200) {
            echo "<p class='success'>✅ AJAX retornou 200 OK</p>";
            echo "<p>Resposta:</p><pre>" . htmlspecialchars(substr($body, 0, 500)) . "</pre>";
        } else {
            echo "<p class='error'>❌ AJAX retornou: $status_code</p>";
            echo "<p>Resposta:</p><pre>" . htmlspecialchars($body) . "</pre>";
        }
    }
}

// ====== 5. VERIFICAR ASSETS ======
echo "<h3>5️⃣ Assets (JS, CSS) enfileirados corretamente?</h3>";

global $wp_scripts, $wp_styles;

if (isset($wp_scripts->registered['handlebars'])) {
    echo "<p class='success'>✅ Handlebars enfileirado</p>";
} else {
    echo "<p class='error'>⚠️ Handlebars NÃO enfileirado</p>";
}

if (isset($wp_scripts->registered['sm-sc-frontend-app'])) {
    echo "<p class='success'>✅ frontend-app.js enfileirado</p>";
} else {
    echo "<p class='error'>⚠️ frontend-app.js NÃO enfileirado</p>";
}

if (isset($wp_styles->registered['sm-sc-frontend-base'])) {
    echo "<p class='success'>✅ frontend-base.css enfileirado</p>";
} else {
    echo "<p class='error'>⚠️ frontend-base.css NÃO enfileirado</p>";
}

// ====== 6. RENDERIZAR SHORTCODE ======
echo "<h3>6️⃣ Renderizar Shortcode</h3>";
echo "<p>HTML gerado:</p>";

ob_start();
echo do_shortcode('[painel_professor]');
$shortcode_output = ob_get_clean();

if (empty($shortcode_output)) {
    echo "<p class='error'>❌ Shortcode retornou vazio!</p>";
} else {
    echo "<p class='success'>✅ Shortcode renderizado</p>";
    echo "<pre>" . htmlspecialchars(substr($shortcode_output, 0, 1000)) . "...</pre>";
}

// ====== 7. VERIFICAR DADOS DO PROFESSOR ======
echo "<h3>7️⃣ Dados do Professor</h3>";

if (class_exists('SM_Student_Control_JWT')) {
    $prof_data = SM_Student_Control_JWT::get_professor_data();
    if ($prof_data) {
        echo "<p class='success'>✅ Dados do professor obtidos</p>";
        echo "<pre>" . json_encode($prof_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<p class='error'>❌ Falha ao obter dados do professor</p>";
    }
}

// ====== 8. VERIFICAR ARQUIVO DE CONFIGURAÇÃO ======
echo "<h3>8️⃣ Configurações salvas no WordPress</h3>";
$settings_keys = [
    'sm_sc_version',
    'sm_sc_items_per_page',
    'sm_sc_debug_mode',
];

foreach ($settings_keys as $key) {
    $value = get_option($key);
    if ($value !== false) {
        echo "<p class='success'>✅ $key = " . json_encode($value) . "</p>";
    }
}

echo "<h3>9️⃣ Verificar Localize Script</h3>";
echo "<p>Procurar por 'smscFrontend' no HTML fonte da página...</p>";
echo "<p class='info'>Abra a página com o shortcode e verifique se no HTML há:</p>";
echo "<pre>&lt;script id='sm-sc-frontend-app-js-extra'&gt;
var smscFrontend = {...}
&lt;/script&gt;</pre>";

echo "</body></html>";
?>
