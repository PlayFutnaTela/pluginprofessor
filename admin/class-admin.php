<?php
/**
 * Classe Admin do Plugin
 * Gerencia páginas administrativas e configurações
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Admin {

    /**
     * Inicializar admin
     */
    public static function init() {
        // Verificar se estamos no admin e WordPress está carregado
        if (!is_admin() || !function_exists('wp_get_current_user')) {
            error_log('[SM-SC-ADMIN] ⚠️ Não estamos no admin ou WordPress não está carregado');
            return;
        }

        error_log('[SM-SC-ADMIN] 👨‍💼 Inicializando interface administrativa');

        add_action('admin_menu', [__CLASS__, 'add_admin_menus']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_filter('plugin_action_links_' . SM_STUDENT_CONTROL_BASENAME, [__CLASS__, 'add_plugin_action_links']);

        error_log('[SM-SC-ADMIN] ✅ Hooks administrativos registrados');
    }

    /**
     * Adicionar menus do admin
     */
    public static function add_admin_menus() {
        error_log('[SM-SC-ADMIN] 📋 Adicionando menus administrativos');

        // Menu principal
        add_menu_page(
            __('Painel Professor', 'sm-student-control'),
            __('Painel Professor', 'sm-student-control'),
            'manage_options',
            'sm-student-control',
            [__CLASS__, 'admin_page_main'],
            'dashicons-groups',
            30
        );
        error_log('[SM-SC-ADMIN] 📄 Menu principal adicionado');

        // Submenu: Configurações
        add_submenu_page(
            'sm-student-control',
            __('Configurações', 'sm-student-control'),
            __('Configurações', 'sm-student-control'),
            'manage_options',
            'sm-student-control-settings',
            [__CLASS__, 'admin_page_settings']
        );

        // Submenu: Gerenciar Cache
        add_submenu_page(
            'sm-student-control',
            __('Gerenciar Cache', 'sm-student-control'),
            __('Cache', 'sm-student-control'),
            'manage_options',
            'sm-student-control-cache',
            [__CLASS__, 'admin_page_cache']
        );

        // Submenu: Shortcode
        add_submenu_page(
            'sm-student-control',
            __('Gerador de Shortcode', 'sm-student-control'),
            __('Shortcode', 'sm-student-control'),
            'manage_options',
            'sm-student-control-shortcode',
            [__CLASS__, 'admin_page_shortcode']
        );

        // Submenu: Logs e Debug
        add_submenu_page(
            'sm-student-control',
            __('Logs e Debug', 'sm-student-control'),
            __('Logs', 'sm-student-control'),
            'manage_options',
            'sm-student-control-logs',
            [__CLASS__, 'admin_page_logs']
        );
    }

    /**
     * Registrar configurações
     */
    public static function register_settings() {
        // Registrar página de configurações
        register_setting('sm_sc_settings', 'sm_sc_version');
        register_setting('sm_sc_settings', 'sm_sc_jwt_secret');
        register_setting('sm_sc_settings', 'sm_sc_api_url');
        register_setting('sm_sc_settings', 'sm_sc_api_token');
        register_setting('sm_sc_settings', 'sm_sc_cache_enabled');
        register_setting('sm_sc_settings', 'sm_sc_cache_duration');
        register_setting('sm_sc_settings', 'sm_sc_items_per_page');
        register_setting('sm_sc_settings', 'sm_sc_debug_mode');

        // Seção: Configurações Gerais
        add_settings_section(
            'sm_sc_general_section',
            __('Configurações Gerais', 'sm-student-control'),
            [__CLASS__, 'settings_section_callback'],
            'sm_sc_settings'
        );

        add_settings_field(
            'sm_sc_version',
            __('Versão do Plugin', 'sm-student-control'),
            [__CLASS__, 'version_field_callback'],
            'sm_sc_settings',
            'sm_sc_general_section'
        );

        add_settings_field(
            'sm_sc_items_per_page',
            __('Itens por Página', 'sm-student-control'),
            [__CLASS__, 'items_per_page_field_callback'],
            'sm_sc_settings',
            'sm_sc_general_section'
        );

        add_settings_field(
            'sm_sc_debug_mode',
            __('Modo Debug', 'sm-student-control'),
            [__CLASS__, 'debug_mode_field_callback'],
            'sm_sc_settings',
            'sm_sc_general_section'
        );

        // Seção: Configurações de API
        add_settings_section(
            'sm_sc_api_section',
            __('Configurações da API', 'sm-student-control'),
            [__CLASS__, 'api_section_callback'],
            'sm_sc_settings'
        );

        add_settings_field(
            'sm_sc_api_url',
            __('URL da API', 'sm-student-control'),
            [__CLASS__, 'api_url_field_callback'],
            'sm_sc_settings',
            'sm_sc_api_section'
        );

        add_settings_field(
            'sm_sc_api_token',
            __('Token da API', 'sm-student-control'),
            [__CLASS__, 'api_token_field_callback'],
            'sm_sc_settings',
            'sm_sc_api_section'
        );

        add_settings_field(
            'sm_sc_jwt_secret',
            __('Chave Secreta JWT', 'sm-student-control'),
            [__CLASS__, 'jwt_secret_field_callback'],
            'sm_sc_settings',
            'sm_sc_api_section'
        );

        // Seção: Configurações de Cache
        add_settings_section(
            'sm_sc_cache_section',
            __('Configurações de Cache', 'sm-student-control'),
            [__CLASS__, 'cache_section_callback'],
            'sm_sc_settings'
        );

        add_settings_field(
            'sm_sc_cache_enabled',
            __('Cache Habilitado', 'sm-student-control'),
            [__CLASS__, 'cache_enabled_field_callback'],
            'sm_sc_settings',
            'sm_sc_cache_section'
        );

        add_settings_field(
            'sm_sc_cache_duration',
            __('Duração do Cache (segundos)', 'sm-student-control'),
            [__CLASS__, 'cache_duration_field_callback'],
            'sm_sc_settings',
            'sm_sc_cache_section'
        );

        error_log('[SM-SC-ADMIN] ✅ Configurações e seções registradas');
    }

    /**
     * Callbacks das seções de configurações
     */
    public static function settings_section_callback() {
        echo '<p>' . __('Configure as opções gerais do plugin.', 'sm-student-control') . '</p>';
    }

    public static function api_section_callback() {
        echo '<p>' . __('Configure as credenciais e endpoints da API externa.', 'sm-student-control') . '</p>';
    }

    public static function cache_section_callback() {
        echo '<p>' . __('Configure as opções de cache para melhorar o desempenho.', 'sm-student-control') . '</p>';
    }

    /**
     * Callbacks dos campos de configuração
     */
    public static function version_field_callback() {
        $value = get_option('sm_sc_version', SM_STUDENT_CONTROL_VERSION);
        echo '<input type="text" name="sm_sc_version" value="' . esc_attr($value) . '" readonly class="regular-text" />';
        echo '<p class="description">' . __('Versão atual do plugin (somente leitura).', 'sm-student-control') . '</p>';
    }

    public static function items_per_page_field_callback() {
        $value = get_option('sm_sc_items_per_page', 20);
        echo '<input type="number" name="sm_sc_items_per_page" value="' . esc_attr($value) . '" min="5" max="100" class="small-text" />';
        echo '<p class="description">' . __('Número de itens a exibir por página nas listagens.', 'sm-student-control') . '</p>';
    }

    public static function debug_mode_field_callback() {
        $value = get_option('sm_sc_debug_mode', '0');
        echo '<input type="checkbox" name="sm_sc_debug_mode" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Habilitar modo debug para logs detalhados.', 'sm-student-control') . '</label>';
    }

    public static function api_url_field_callback() {
        $value = get_option('sm_sc_api_url', '');
        echo '<input type="url" name="sm_sc_api_url" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://api.exemplo.com" />';
        echo '<p class="description">' . __('URL base da API externa do LMS.', 'sm-student-control') . '</p>';
    }

    public static function api_token_field_callback() {
        $value = get_option('sm_sc_api_token', '');
        echo '<input type="password" name="sm_sc_api_token" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Token de autenticação para acessar a API.', 'sm-student-control') . '</p>';
    }

    public static function jwt_secret_field_callback() {
        $value = get_option('sm_sc_jwt_secret', '');
        echo '<input type="password" name="sm_sc_jwt_secret" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<button type="button" id="generate-jwt-secret" class="button button-secondary">' . __('Gerar Nova Chave', 'sm-student-control') . '</button>';
        echo '<p class="description">' . __('Chave secreta para geração de tokens JWT.', 'sm-student-control') . '</p>';
    }

    public static function cache_enabled_field_callback() {
        $value = get_option('sm_sc_cache_enabled', '1');
        echo '<input type="checkbox" name="sm_sc_cache_enabled" value="1" ' . checked(1, $value, false) . ' />';
        echo '<label>' . __('Habilitar cache para melhorar o desempenho.', 'sm-student-control') . '</label>';
    }

    public static function cache_duration_field_callback() {
        $value = get_option('sm_sc_cache_duration', 3600);
        echo '<input type="number" name="sm_sc_cache_duration" value="' . esc_attr($value) . '" min="300" max="86400" class="small-text" />';
        echo '<p class="description">' . __('Duração do cache em segundos (padrão: 3600 = 1 hora).', 'sm-student-control') . '</p>';
    }

    /**
     * Páginas do admin
     */
    public static function admin_page_main() {
        error_log('[SM-SC-ADMIN] 🎯 Chamando admin_page_main() - incluindo main-dashboard.php');
        include SM_STUDENT_CONTROL_DIR . 'admin/views/main-dashboard.php';
        error_log('[SM-SC-ADMIN] ✅ main-dashboard.php incluído com sucesso');
    }

    public static function admin_page_settings() {
        include SM_STUDENT_CONTROL_DIR . 'admin/views/settings-page.php';
    }

    public static function admin_page_cache() {
        include SM_STUDENT_CONTROL_DIR . 'admin/views/cache-management.php';
    }

    public static function admin_page_shortcode() {
        include SM_STUDENT_CONTROL_DIR . 'admin/views/shortcode-generator.php';
    }

    public static function admin_page_logs() {
        include SM_STUDENT_CONTROL_DIR . 'admin/views/logs-debug.php';
    }

    /**
     * Enqueue assets do admin
     */
    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'sm-student-control') === false) {
            return;
        }

        wp_enqueue_style(
            'sm-sc-admin',
            SM_STUDENT_CONTROL_URL . 'admin/assets/css/admin-styles.css',
            [],
            SM_STUDENT_CONTROL_VERSION
        );

        wp_enqueue_script(
            'sm-sc-admin',
            SM_STUDENT_CONTROL_URL . 'admin/assets/js/admin-scripts.js',
            ['jquery'],
            SM_STUDENT_CONTROL_VERSION,
            true
        );

        wp_localize_script('sm-sc-admin', 'smscAdmin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sm_sc_admin_nonce'),
            'strings' => [
                'confirm_clear_cache' => __('Tem certeza que deseja limpar todo o cache?', 'sm-student-control'),
                'cache_cleared' => __('Cache limpo com sucesso!', 'sm-student-control'),
                'error_occurred' => __('Ocorreu um erro. Tente novamente.', 'sm-student-control'),
            ]
        ]);
    }

    /**
     * Adicionar links de ação no plugin
     */
    public static function add_plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=sm-student-control-settings') . '">' . __('Configurações', 'sm-student-control') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * AJAX: Limpar cache
     */
    public static function ajax_clear_cache() {
        check_ajax_referer('sm_sc_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Acesso negado', 'sm-student-control'));
        }

        $result = SM_Student_Control_Cache::clear_all_cache();

        wp_send_json([
            'success' => true,
            'message' => sprintf(__('Cache limpo com sucesso! %d itens removidos.', 'sm-student-control'), $result),
            'items_cleared' => $result
        ]);
    }

    /**
     * AJAX: Testar API
     */
    public static function ajax_test_api() {
        check_ajax_referer('sm_sc_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Acesso negado', 'sm-student-control'));
        }

        $result = SM_Student_Control_External_API::test_connection();

        wp_send_json($result);
    }
}

// REMOVIDO: Inicialização condicional movida para o loader