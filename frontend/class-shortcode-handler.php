<?php
/**
 * Shortcode Handler
 *
 * Gerencia o shortcode [painel_professor]
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Shortcode_Handler {

    /**
     * Inicializar shortcode
     */
    public static function init() {
        // Verificar se WordPress está totalmente carregado
        if (!function_exists('wp_get_current_user') || !function_exists('add_shortcode')) {
            error_log('[SM-SC-SHORTCODE] ⚠️ WordPress ainda não carregado, pulando registro do shortcode');
            return;
        }

        error_log('[SM-SC-SHORTCODE] 🎯 Registrando shortcode [painel_professor]');
        add_shortcode('painel_professor', [__CLASS__, 'render_shortcode']);
        error_log('[SM-SC-SHORTCODE] ✅ Shortcode registrado com sucesso');
    }

    /**
     * Renderizar shortcode
     */
    public static function render_shortcode($atts) {
        error_log('[SM-SC-SHORTCODE] 🎨 Iniciando renderização do shortcode [painel_professor]');
        error_log('[SM-SC-SHORTCODE] 📝 Atributos recebidos: ' . json_encode($atts));

        // Verificar se usuário está logado
        if (!is_user_logged_in()) {
            error_log('[SM-SC-SHORTCODE] 🚫 Usuário não logado, mostrando tela de login');
            return self::render_login_required();
        }

        // Verificar JWT e permissões
        $professor_data = SM_Student_Control_JWT::get_professor_data();
        if (!$professor_data) {
            error_log('[SM-SC-SHORTCODE] 🚫 Dados do professor não encontrados ou token inválido');
            return self::render_not_authorized();
        }

        error_log('[SM-SC-SHORTCODE] ✅ Professor autenticado: ' . json_encode($professor_data));

        // Parse attributes
        $atts = self::parse_attributes($atts);
        error_log('[SM-SC-SHORTCODE] ⚙️ Atributos processados: ' . json_encode($atts));

        // Enqueue assets
        self::enqueue_assets($atts);

        // Iniciar output buffering
        ob_start();

        // Carregar template
        self::load_template($atts, $professor_data);

        $output = ob_get_clean();
        error_log('[SM-SC-SHORTCODE] ✅ Shortcode renderizado com sucesso, tamanho: ' . strlen($output) . ' caracteres');

        return $output;
    }

    /**
     * Parse e validar atributos do shortcode
     */
    private static function parse_attributes($atts) {
        $defaults = SM_Student_Control_Settings::get_shortcode_defaults();

        return shortcode_atts($defaults, $atts, 'painel_professor');
    }

    /**
     * Enqueue assets do frontend
     */
    private static function enqueue_assets($atts) {
        // CSS base
        wp_enqueue_style(
            'sm-sc-frontend-base',
            SM_STUDENT_CONTROL_URL . 'frontend/assets/css/frontend-base.css',
            [],
            SM_STUDENT_CONTROL_VERSION
        );

        // CSS do tema
        $theme_css = 'frontend/assets/css/theme-' . $atts['template'] . '.css';
        if (file_exists(SM_STUDENT_CONTROL_DIR . $theme_css)) {
            wp_enqueue_style(
                'sm-sc-frontend-theme',
                SM_STUDENT_CONTROL_URL . $theme_css,
                ['sm-sc-frontend-base'],
                SM_STUDENT_CONTROL_VERSION
            );
        }

        // Handlebars (para templates)
        wp_enqueue_script(
            'handlebars',
            'https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.7/handlebars.min.js',
            [],
            '4.7.7',
            false
        );

        // JavaScript
        wp_enqueue_script(
            'sm-sc-frontend-app',
            SM_STUDENT_CONTROL_URL . 'frontend/assets/js/frontend-app.js',
            ['jquery', 'handlebars'],
            SM_STUDENT_CONTROL_VERSION,
            true
        );

        // Localizar script
        wp_localize_script('sm-sc-frontend-app', 'smscFrontend', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sm_sc_frontend_nonce'),
            'strings' => [
                'loading' => __('Carregando...', 'sm-student-control'),
                'error' => __('Erro ao carregar dados', 'sm-student-control'),
                'no_students' => __('Nenhum aluno encontrado', 'sm-student-control'),
                'confirm_refresh' => __('Atualizar cache deste aluno?', 'sm-student-control'),
            ],
            'atts' => $atts,
        ]);

        // Inline CSS para customizações
        $custom_css = self::generate_custom_css($atts);
        if (!empty($custom_css)) {
            wp_add_inline_style('sm-sc-frontend-base', $custom_css);
        }
    }

    /**
     * Gerar CSS customizado baseado nos atributos
     */
    private static function generate_custom_css($atts) {
        $css = '';

        if (!empty($atts['theme_color'])) {
            $css .= "
                .sm-sc-frontend {
                    --primary-color: {$atts['theme_color']};
                }
                .sm-sc-frontend .button.primary {
                    background: {$atts['theme_color']};
                }
                .sm-sc-frontend .button.primary:hover {
                    background: " . self::adjust_color_brightness($atts['theme_color'], -20) . ";
                }
            ";
        }

        if (!empty($atts['width'])) {
            $css .= "
                .sm-sc-frontend {
                    width: {$atts['width']};
                    max-width: 100%;
                }
            ";
        }

        return $css;
    }

    /**
     * Ajustar brilho da cor
     */
    private static function adjust_color_brightness($hex, $steps) {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust brightness
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));

        // Convert back to hex
        return '#' . str_pad(dechex($r), 2, '0') .
               str_pad(dechex($g), 2, '0') .
               str_pad(dechex($b), 2, '0');
    }

    /**
     * Carregar template
     */
    private static function load_template($atts, $professor_data) {
        $template_name = sanitize_file_name($atts['template']);
        $template_file = SM_STUDENT_CONTROL_DIR . "frontend/templates/theme-{$template_name}/dashboard.php";

        if (!file_exists($template_file)) {
            $template_file = SM_STUDENT_CONTROL_DIR . 'frontend/templates/theme-default/dashboard.php';
        }

        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo '<div class="sm-sc-error">' . __('Template não encontrado', 'sm-student-control') . '</div>';
        }
    }

    /**
     * Renderizar dashboard
     */
    public static function render_dashboard($atts, $professor_data) {
        $template_name = sanitize_file_name($atts['template']);
        $template_file = SM_STUDENT_CONTROL_DIR . "frontend/templates/theme-{$template_name}/dashboard.php";

        if (!file_exists($template_file)) {
            $template_file = SM_STUDENT_CONTROL_DIR . 'frontend/templates/theme-default/dashboard.php';
        }

        if (file_exists($template_file)) {
            include $template_file;
        }
    }

    /**
     * Renderizar lista de estudantes
     */
    public static function render_students_list($atts, $professor_data) {
        $template_name = sanitize_file_name($atts['template']);
        $template_file = SM_STUDENT_CONTROL_DIR . "frontend/templates/theme-{$template_name}/students-list.php";

        if (!file_exists($template_file)) {
            $template_file = SM_STUDENT_CONTROL_DIR . 'frontend/templates/theme-default/students-list.php';
        }

        if (file_exists($template_file)) {
            include $template_file;
        }
    }

    /**
     * Renderizar detalhes do estudante
     */
    public static function render_student_details($student_id, $atts) {
        if (empty($student_id)) {
            return '<div class="sm-sc-error">' . __('ID do aluno não informado', 'sm-student-control') . '</div>';
        }

        $template_name = sanitize_file_name($atts['template']);
        $template_file = SM_STUDENT_CONTROL_DIR . "frontend/templates/theme-{$template_name}/student-details.php";

        if (!file_exists($template_file)) {
            $template_file = SM_STUDENT_CONTROL_DIR . 'frontend/templates/theme-default/student-details.php';
        }

        if (file_exists($template_file)) {
            $student_id = intval($student_id);
            include $template_file;
        } else {
            return '<div class="sm-sc-error">' . __('Template não encontrado', 'sm-student-control') . '</div>';
        }
    }

    /**
     * Mensagem de login necessário
     */
    private static function render_login_required() {
        return '<div class="sm-sc-message sm-sc-login-required">
            <div class="sm-sc-message-content">
                <h3>' . __('Acesso Restrito', 'sm-student-control') . '</h3>
                <p>' . __('Você precisa estar logado para acessar o painel do professor.', 'sm-student-control') . '</p>
                <a href="' . wp_login_url(get_permalink()) . '" class="button primary">
                    ' . __('Fazer Login', 'sm-student-control') . '
                </a>
            </div>
        </div>';
    }

    /**
     * Mensagem de não autorizado
     */
    private static function render_not_authorized() {
        return '<div class="sm-sc-message sm-sc-not-authorized">
            <div class="sm-sc-message-content">
                <h3>' . __('Acesso Negado', 'sm-student-control') . '</h3>
                <p>' . __('Você não tem permissão para acessar este painel.', 'sm-student-control') . '</p>
            </div>
        </div>';
    }

    /**
     * AJAX: Carregar estudantes
     */
    public static function ajax_load_students() {
        // Verificar nonce com segurança
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, 'sm_sc_frontend_nonce')) {
            wp_send_json_error(__('Nonce inválido', 'sm-student-control'), 403);
        }

        try {
            if (!class_exists('SM_Student_Control_JWT')) {
                wp_send_json_error(__('Classe JWT não carregada', 'sm-student-control'), 500);
            }

            $professor_data = SM_Student_Control_JWT::get_professor_data();
            if (!$professor_data) {
                wp_send_json_error(__('Acesso negado', 'sm-student-control'), 403);
            }
        } catch (Exception $e) {
            error_log('[SM-SC-AJAX] Erro ao obter dados do professor: ' . $e->getMessage());
            wp_send_json_error(__('Erro ao carregar dados', 'sm-student-control'), 500);
        }

        $args = [
            'search' => sanitize_text_field($_POST['search'] ?? ''),
            'course_id' => intval($_POST['course_id'] ?? 0),
            'page' => intval($_POST['page'] ?? 1),
            'per_page' => intval($_POST['per_page'] ?? 20),
        ];

        // Calcular offset
        $args['offset'] = ($args['page'] - 1) * $args['per_page'];
        $args['limit'] = $args['per_page'];

        try {
            if (!class_exists('SM_Student_Control_Professor_Students')) {
                wp_send_json_error(__('Classe de Professores não carregada', 'sm-student-control'), 500);
            }

            $students_raw = SM_Student_Control_Professor_Students::get_professor_students(
                $professor_data['school_id'],
                $professor_data['professor_id'],
                $args
            );

            // Transformar dados para a tabela
            $students = [];
            if (!empty($students_raw)) {
                foreach ($students_raw as $student) {
                    $students[] = [
                        'id' => isset($student['id']) ? $student['id'] : (isset($student['student_id']) ? $student['student_id'] : 0),
                        'display_name' => isset($student['display_name']) ? $student['display_name'] : (isset($student['name']) ? $student['name'] : 'N/A'),
                        'user_email' => isset($student['user_email']) ? $student['user_email'] : (isset($student['email']) ? $student['email'] : ''),
                        'avatar' => isset($student['avatar']) ? $student['avatar'] : '',
                        'progress' => isset($student['progress_percent']) ? intval($student['progress_percent']) : (isset($student['progress']) ? intval($student['progress']) : 0),
                        'courses_count' => isset($student['courses']) ? count($student['courses']) : 0,
                        'current_course' => isset($student['current_course']) ? $student['current_course'] : '',
                        'last_login' => isset($student['last_login']) ? $student['last_login'] : '',
                    ];
                }
            }

            $total_students = SM_Student_Control_Professor_Students::count_professor_students(
                $professor_data['school_id'],
                $professor_data['professor_id']
            );

            wp_send_json_success([
                'students' => $students,
                'total' => $total_students,
                'page' => $args['page'],
                'per_page' => $args['per_page'],
                'total_pages' => ceil($total_students / $args['per_page']),
            ]);
        } catch (Exception $e) {
            error_log('[SM-SC-AJAX] Erro ao carregar estudantes: ' . $e->getMessage());
            wp_send_json_error(__('Erro ao carregar estudantes: ' . $e->getMessage(), 'sm-student-control'), 500);
        }
    }

    /**
     * AJAX: Carregar detalhes do estudante
     */
    public static function ajax_load_student_details() {
        check_ajax_referer('sm_sc_frontend_nonce', 'nonce');

        $student_id = intval($_POST['student_id'] ?? 0);

        if (!$student_id) {
            wp_send_json_error(__('ID do aluno não informado', 'sm-student-control'));
        }

        // Verificar permissões
        if (!SM_Student_Control_Professor_Students::professor_has_access_to_student(
            get_current_user_id(),
            $student_id
        )) {
            wp_send_json_error(__('Acesso negado a este aluno', 'sm-student-control'));
        }

        $student = SM_Student_Control_Professor_Students::get_student_details($student_id);

        if (!$student) {
            wp_send_json_error(__('Aluno não encontrado', 'sm-student-control'));
        }

        wp_send_json_success($student);
    }

    /**
     * AJAX: Atualizar cache do estudante
     */
    public static function ajax_refresh_student_cache() {
        check_ajax_referer('sm_sc_frontend_nonce', 'nonce');

        $student_id = intval($_POST['student_id'] ?? 0);

        if (!$student_id) {
            wp_send_json_error(__('ID do aluno não informado', 'sm-student-control'));
        }

        // Verificar permissões
        if (!SM_Student_Control_Professor_Students::professor_has_access_to_student(
            get_current_user_id(),
            $student_id
        )) {
            wp_send_json_error(__('Acesso negado', 'sm-student-control'));
        }

        $result = SM_Student_Control_Cache::refresh_student_cache($student_id);

        if ($result) {
            wp_send_json_success(__('Cache atualizado com sucesso', 'sm-student-control'));
        } else {
            wp_send_json_error(__('Erro ao atualizar cache', 'sm-student-control'));
        }
    }
}

// REMOVIDO: Inicialização e hooks AJAX movidos para o loader