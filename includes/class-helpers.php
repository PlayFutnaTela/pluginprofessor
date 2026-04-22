<?php
/**
 * Helpers
 *
 * Funções utilitárias para o plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Helpers {

    /**
     * Formatar data para exibição
     */
    public static function format_date($date_string, $format = null) {
        if (empty($date_string)) {
            return '';
        }

        if ($format === null) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        $timestamp = strtotime($date_string);

        if ($timestamp === false) {
            return $date_string;
        }

        return date_i18n($format, $timestamp);
    }

    /**
     * Obter iniciais do nome
     */
    public static function get_initials($name) {
        if (empty($name)) {
            return '?';
        }

        $parts = explode(' ', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    /**
     * Obter label do status do usuário
     */
    public static function get_user_status_label($status) {
        $labels = [
            'active' => __('Ativo', 'sm-student-control'),
            'inactive' => __('Inativo', 'sm-student-control'),
            'pending' => __('Pendente', 'sm-student-control'),
            'suspended' => __('Suspenso', 'sm-student-control'),
        ];

        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }

    /**
     * Obter label do status do curso
     */
    public static function get_course_status_label($status) {
        $labels = [
            'enrolled' => __('Matriculado', 'sm-student-control'),
            'in_progress' => __('Em Andamento', 'sm-student-control'),
            'completed' => __('Concluído', 'sm-student-control'),
            'failed' => __('Reprovado', 'sm-student-control'),
            'dropped' => __('Desistiu', 'sm-student-control'),
        ];

        return isset($labels[$status]) ? $labels[$status] : ucfirst($status);
    }

    /**
     * Obter ícone para tipo de atividade
     */
    public static function get_activity_icon($activity_type) {
        $icons = [
            'lesson_completed' => 'dashicons-yes',
            'course_started' => 'dashicons-playlist-audio',
            'course_completed' => 'dashicons-awards',
            'quiz_attempted' => 'dashicons-editor-help',
            'assignment_submitted' => 'dashicons-upload',
            'login' => 'dashicons-admin-users',
            'certificate_earned' => 'dashicons-awards',
        ];

        return isset($icons[$activity_type]) ? $icons[$activity_type] : 'dashicons-admin-generic';
    }

    /**
     * Formatar tempo em horas:minutos
     */
    public static function format_time($seconds) {
        if (!is_numeric($seconds)) {
            return '0h 0m';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return sprintf(__('%dh %dm', 'sm-student-control'), $hours, $minutes);
        } else {
            return sprintf(__('%dm', 'sm-student-control'), $minutes);
        }
    }

    /**
     * Calcular progresso percentual
     */
    public static function calculate_progress($completed, $total) {
        if (!is_numeric($completed) || !is_numeric($total) || $total == 0) {
            return 0;
        }

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Sanitizar e validar array de IDs
     */
    public static function sanitize_ids_array($ids) {
        if (!is_array($ids)) {
            return [];
        }

        return array_filter(array_map('intval', $ids));
    }

    /**
     * Verificar se string é email válido
     */
    public static function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Truncar texto com ellipsis
     */
    public static function truncate_text($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - strlen($suffix)) . $suffix;
    }

    /**
     * Obter URL do avatar do usuário
     */
    public static function get_user_avatar_url($user_id, $size = 96) {
        $avatar_url = get_avatar_url($user_id, ['size' => $size]);

        if (!$avatar_url) {
            // Fallback para avatar padrão
            $avatar_url = SM_STUDENT_CONTROL_URL . 'frontend/assets/images/default-avatar.png';
        }

        return $avatar_url;
    }

    /**
     * Converter array para CSV
     */
    public static function array_to_csv($data, $headers = []) {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Headers
        if (!empty($headers)) {
            fputcsv($output, $headers);
        } elseif (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }

        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Gerar hash único para cache
     */
    public static function generate_cache_key($prefix, $data) {
        $data_string = is_array($data) ? serialize($data) : $data;
        return $prefix . '_' . md5($data_string);
    }

    /**
     * Verificar se é uma requisição AJAX
     */
    public static function is_ajax_request() {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Obter IP do usuário
     */
    public static function get_user_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Para X-Forwarded-For, pegar o primeiro IP
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validar IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * Log de debug
     */
    public static function debug_log($message, $data = null) {
        if (!SM_Student_Control_Settings::get_setting('debug_mode')) {
            return;
        }

        $log_message = '[' . date('Y-m-d H:i:s') . '] ' . $message;

        if ($data !== null) {
            $log_message .= ' | Data: ' . (is_array($data) || is_object($data) ? json_encode($data) : $data);
        }

        error_log($log_message, 3, SM_STUDENT_CONTROL_DIR . 'logs/debug.log');
    }

    /**
     * Limpar dados de entrada
     */
    public static function sanitize_input($data, $type = 'string') {
        switch ($type) {
            case 'email':
                return sanitize_email($data);
            case 'url':
                return esc_url_raw($data);
            case 'int':
                return intval($data);
            case 'float':
                return floatval($data);
            case 'array':
                return is_array($data) ? array_map('sanitize_text_field', $data) : [];
            case 'html':
                return wp_kses_post($data);
            case 'string':
            default:
                return sanitize_text_field($data);
        }
    }

    /**
     * Verificar permissões do usuário atual
     */
    public static function current_user_can($capability) {
        if (!is_user_logged_in()) {
            return false;
        }

        return current_user_can($capability);
    }

    /**
     * Obter dados do usuário atual
     */
    public static function get_current_user_data() {
        if (!is_user_logged_in()) {
            return null;
        }

        $user = wp_get_current_user();

        return [
            'id' => $user->ID,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
        ];
    }

    /**
     * Verificar se usuário é professor
     */
    public static function is_professor($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        return user_can($user_id, 'manage_options') || user_can($user_id, 'sm_student_control_professor');
    }
}