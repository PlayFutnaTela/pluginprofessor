<?php
/**
 * Loader principal do plugin
 * Responsável por inicializar todas as classes e componentes
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Loader {

    /**
     * Inicializar o plugin
     */
    public static function init() {
        // Verificar se WordPress está totalmente carregado
        if (!function_exists('wp_get_current_user') || !function_exists('is_admin')) {
            error_log('[SM-SC-LOADER] ⚠️ WordPress ainda não está totalmente carregado, adiando inicialização');
            return;
        }

        error_log('[SM-SC-LOADER] 🎯 Inicializando loader do plugin');
        self::load_dependencies();
        self::init_components();
        self::init_hooks();
        error_log('[SM-SC-LOADER] ✅ Loader inicializado com sucesso');
    }

    /**
     * Inicializar componentes
     */
    private static function init_components() {
        error_log('[SM-SC-LOADER] 🔧 Inicializando componentes...');

        // Inicializar JWT
        SM_Student_Control_JWT::init();
        error_log('[SM-SC-LOADER] ✅ JWT inicializado');

        // Inicializar API externa
        SM_Student_Control_External_API::init();
        error_log('[SM-SC-LOADER] ✅ API externa inicializada');

        // Inicializar cache
        SM_Student_Control_Cache::init();
        error_log('[SM-SC-LOADER] ✅ Cache inicializado');

        // Inicializar admin (apenas se estiver no admin)
        if (is_admin()) {
            SM_Student_Control_Admin::init();
            error_log('[SM-SC-LOADER] ✅ Admin inicializado');
        }

        // Inicializar shortcode handler
        SM_Student_Control_Shortcode_Handler::init();
        error_log('[SM-SC-LOADER] ✅ Shortcode handler inicializado');

        error_log('[SM-SC-LOADER] 🔧 Todos os componentes inicializados');
    }

    /**
     * Carregar dependências
     */
    private static function load_dependencies() {
        error_log('[SM-SC-LOADER] 📦 Carregando dependências...');

        // Classes core
        error_log('[SM-SC-LOADER] 🔧 Carregando classes core...');
        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-data.php';
        error_log('[SM-SC-LOADER] ✅ class-data.php carregada');

        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-external-api.php';
        error_log('[SM-SC-LOADER] ✅ class-external-api.php carregada');

        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-jwt.php';
        error_log('[SM-SC-LOADER] ✅ class-jwt.php carregada');

        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-cache.php';
        error_log('[SM-SC-LOADER] ✅ class-cache.php carregada');

        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-professor-students.php';
        error_log('[SM-SC-LOADER] ✅ class-professor-students.php carregada');

        // Admin
        if (is_admin()) {
            error_log('[SM-SC-LOADER] 👨‍💼 Carregando interface admin...');
            require_once SM_STUDENT_CONTROL_DIR . 'admin/class-admin.php';
            error_log('[SM-SC-LOADER] ✅ class-admin.php carregada');
        }

        // Frontend
        error_log('[SM-SC-LOADER] 🎨 Carregando frontend...');
        require_once SM_STUDENT_CONTROL_DIR . 'frontend/class-shortcode-handler.php';
        error_log('[SM-SC-LOADER] ✅ class-shortcode-handler.php carregada');

        // API
        error_log('[SM-SC-LOADER] 🌐 Carregando API REST...');
        require_once SM_STUDENT_CONTROL_DIR . 'includes/class-rest-api.php';
        error_log('[SM-SC-LOADER] ✅ class-rest-api.php carregada');

        error_log('[SM-SC-LOADER] 📦 Todas as dependências carregadas');
    }

    /**
     * Inicializar hooks
     */
    private static function init_hooks() {
        error_log('[SM-SC-LOADER] 🔗 Inicializando hooks...');

        // Hooks de inicialização
        add_action('init', [__CLASS__, 'load_textdomain']);
        error_log('[SM-SC-LOADER] 🔗 Hook textdomain registrado');

        // Inicializar API REST
        SM_Student_Control_REST_API::init();
        error_log('[SM-SC-LOADER] 🔗 API REST inicializada');

        // Enqueue scripts/styles
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        error_log('[SM-SC-LOADER] 🔗 Hooks de assets registrados');

        // AJAX handlers
        add_action('wp_ajax_sm_sc_refresh_cache', [__CLASS__, 'ajax_refresh_cache']);
        add_action('wp_ajax_sm_sc_export_students', [__CLASS__, 'ajax_export_students']);
        add_action('wp_ajax_sm_sc_load_students', ['SM_Student_Control_Shortcode_Handler', 'ajax_load_students']);
        add_action('wp_ajax_sm_sc_load_student_details', ['SM_Student_Control_Shortcode_Handler', 'ajax_load_student_details']);
        add_action('wp_ajax_sm_sc_refresh_student_cache', ['SM_Student_Control_Shortcode_Handler', 'ajax_refresh_student_cache']);
        error_log('[SM-SC-LOADER] 🔗 Handlers AJAX registrados');

        error_log('[SM-SC-LOADER] 🔗 Todos os hooks inicializados');
    }

    /**
     * Carregar textdomain para internacionalização
     */
    public static function load_textdomain() {
        load_plugin_textdomain(
            'sm-student-control',
            false,
            dirname(SM_STUDENT_CONTROL_BASENAME) . '/languages'
        );
    }

    /**
     * Enqueue assets do frontend (apenas quando necessário)
     */
    public static function enqueue_frontend_assets() {
        global $post;

        // Verificar se a página contém o shortcode
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'painel_professor')) {
            wp_enqueue_style(
                'sm-sc-frontend',
                SM_STUDENT_CONTROL_URL . 'frontend/assets/css/frontend-base.css',
                [],
                SM_STUDENT_CONTROL_VERSION
            );

            wp_enqueue_script(
                'sm-sc-frontend-app',
                SM_STUDENT_CONTROL_URL . 'frontend/assets/js/frontend-app.js',
                ['jquery'],
                SM_STUDENT_CONTROL_VERSION,
                true
            );

            // Localizar script
            wp_localize_script('sm-sc-frontend-app', 'smscAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sm_sc_ajax_nonce'),
                'strings' => [
                    'loading' => __('Carregando...', 'sm-student-control'),
                    'error' => __('Erro ao carregar dados', 'sm-student-control'),
                    'no_students' => __('Nenhum aluno encontrado', 'sm-student-control'),
                ]
            ]);
        }
    }

    /**
     * Enqueue assets do admin
     */
    public static function enqueue_admin_assets($hook) {
        // Apenas nas páginas do plugin
        if (strpos($hook, 'sm-student-control') !== false) {
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

            wp_localize_script('sm-sc-admin', 'smscAdminAjax', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('sm_sc_admin_ajax_nonce'),
            ]);
        }
    }

    /**
     * AJAX: Refresh cache
     */
    public static function ajax_refresh_cache() {
        check_ajax_referer('sm_sc_ajax_nonce', 'nonce');

        $student_id = intval($_POST['student_id'] ?? 0);

        if ($student_id > 0) {
            $result = SM_Student_Control_Cache::refresh_student_cache($student_id);
        } else {
            $result = SM_Student_Control_Cache::refresh_all_cache();
        }

        wp_send_json($result);
    }

    /**
     * AJAX: Export students
     */
    public static function ajax_export_students() {
        check_ajax_referer('sm_sc_ajax_nonce', 'nonce');

        // Implementar export
        $export_url = SM_Student_Control_Data::export_students_csv();

        wp_send_json(['success' => true, 'url' => $export_url]);
    }

    /**
     * Ativação do plugin
     */
    public static function activate() {
        // Criar tabelas se necessário
        self::create_tables();

        // Agendar cron job se necessário
        if (!wp_next_scheduled('sm_sc_daily_cache_refresh')) {
            wp_schedule_event(time(), 'daily', 'sm_sc_daily_cache_refresh');
        }
    }

    /**
     * Desativação do plugin
     */
    public static function deactivate() {
        // Limpar cache
        SM_Student_Control_Cache::clear_all_cache();

        // Remover cron job
        wp_clear_scheduled_hook('sm_sc_daily_cache_refresh');
    }

    /**
     * Criar tabelas necessárias
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Tabela para cache de alunos (se necessário)
        $table_name = $wpdb->prefix . 'sm_sc_cache';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            student_id bigint(20) NOT NULL,
            data longtext NOT NULL,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY student_id (student_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}