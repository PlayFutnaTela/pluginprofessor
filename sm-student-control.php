<?php
/**
 * Plugin Name: Painel Professor - SM Student Control
 * Plugin URI: https://epiccentro.com.br
 * Description: Plugin WordPress para professores gerenciarem alunos do MasterStudy LMS via shortcode. Interface separada entre backend admin e frontend público.
 * Version: 2.0.0
 * Author: EPIC Centro
 * Author URI: https://epiccentro.com.br
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sm-student-control
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// Prevenir acesso direto
if (!defined('ABSPATH')) {
    error_log('[SM-SC] 🚫 Tentativa de acesso direto ao arquivo principal bloqueada');
    exit;
}

// Definir constantes
define('SM_STUDENT_CONTROL_VERSION', '2.0.0');
define('SM_STUDENT_CONTROL_DIR', plugin_dir_path(__FILE__));
define('SM_STUDENT_CONTROL_URL', plugin_dir_url(__FILE__));
define('SM_STUDENT_CONTROL_BASENAME', plugin_basename(__FILE__));

error_log('[SM-SC] 🚀 Plugin inicializado - Versão: ' . SM_STUDENT_CONTROL_VERSION);
error_log('[SM-SC] 📁 Diretório do plugin: ' . SM_STUDENT_CONTROL_DIR);
error_log('[SM-SC] 🌐 URL do plugin: ' . SM_STUDENT_CONTROL_URL);

// Incluir classes principais
error_log('[SM-SC] 📦 Carregando classes principais...');
require_once SM_STUDENT_CONTROL_DIR . 'includes/class-loader.php';
require_once SM_STUDENT_CONTROL_DIR . 'includes/class-settings.php';
error_log('[SM-SC] ✅ Classes principais carregadas');

// Inicializar o plugin
function sm_student_control_init() {
    // Verificar se WordPress está totalmente carregado
    if (!function_exists('wp_get_current_user')) {
        return;
    }

    error_log('[SM-SC] 🎯 Inicializando plugin principal');
    SM_Student_Control_Loader::init();
    error_log('[SM-SC] ✅ Plugin inicializado com sucesso');
}
add_action('plugins_loaded', 'sm_student_control_init', 20);

// Hooks de ativação/desativação
register_activation_hook(__FILE__, 'sm_student_control_activate');
register_deactivation_hook(__FILE__, 'sm_student_control_deactivate');

function sm_student_control_activate() {
    error_log('[SM-SC] 🔧 Iniciando ativação do plugin');

    // Criar tabelas se necessário
    SM_Student_Control_Loader::activate();
    error_log('[SM-SC] 🗄️ Tabelas criadas/atualizadas');

    // Definir configurações padrão
    $default_settings = array(
        'version' => SM_STUDENT_CONTROL_VERSION,
        'jwt_secret' => wp_generate_password(32, false),
        'api_url' => '',
        'api_token' => '',
        'cache_enabled' => true,
        'cache_duration' => 3600, // 1 hora
        'items_per_page' => 20,
        'debug_mode' => false,
    );

    foreach ($default_settings as $key => $value) {
        if (!get_option('sm_sc_' . $key)) {
            update_option('sm_sc_' . $key, $value);
            error_log('[SM-SC] ⚙️ Configuração padrão definida: ' . $key . ' = ' . (is_array($value) ? json_encode($value) : $value));
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();
    error_log('[SM-SC] 🔄 Rewrite rules atualizadas');
    error_log('[SM-SC] ✅ Plugin ativado com sucesso');
}

function sm_student_control_deactivate() {
    error_log('[SM-SC] 🛑 Iniciando desativação do plugin');

    // Limpar cache
    SM_Student_Control_Loader::deactivate();
    error_log('[SM-SC] 🧹 Cache limpo durante desativação');

    // Flush rewrite rules
    flush_rewrite_rules();
    error_log('[SM-SC] 🔄 Rewrite rules atualizadas');
    error_log('[SM-SC] ✅ Plugin desativado com sucesso');
}