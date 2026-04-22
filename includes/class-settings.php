<?php
/**
 * Gerenciamento de configurações do plugin
 * Classe segura para salvar/carregar configurações
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Settings {

    /**
     * Prefixo para todas as opções
     */
    const OPTION_PREFIX = 'sm_sc_';

    /**
     * Configurações padrão
     */
    private static $defaults = [
        'version' => SM_STUDENT_CONTROL_VERSION,
        'jwt_secret' => '',
        'api_url' => '',
        'api_token' => '',
        'cache_enabled' => true,
        'cache_duration' => 3600, // 1 hora
        'items_per_page' => 20,
        'debug_mode' => false,
        'theme_color' => '#4CAF50',
        'show_filters' => true,
        'show_search' => true,
        'show_pagination' => true,
        'template' => 'default',
        'width' => '100%',
    ];

    /**
     * Obter uma configuração
     */
    public static function get($key, $default = null) {
        $option_key = self::OPTION_PREFIX . $key;
        $value = get_option($option_key, $default);

        // Se não tem valor salvo, usar default
        if ($value === null && isset(self::$defaults[$key])) {
            $value = self::$defaults[$key];
        }

        // Log apenas se debug_mode estiver ativado (evitar recursão)
        $debug_mode = get_option(self::OPTION_PREFIX . 'debug_mode', false);
        if ($debug_mode) {
            error_log('[SM-SC-SETTINGS] 📖 Configuração lida: ' . $key . ' = ' . (is_array($value) ? json_encode($value) : $value));
        }

        return $value;
    }

    /**
     * Salvar uma configuração
     */
    public static function set($key, $value) {
        $option_key = self::OPTION_PREFIX . $key;

        // Sanitizar baseado no tipo
        $value = self::sanitize_value($key, $value);

        return update_option($option_key, $value);
    }

    /**
     * Salvar múltiplas configurações
     */
    public static function set_multiple($settings) {
        $updated = 0;

        foreach ($settings as $key => $value) {
            if (self::set($key, $value)) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Obter todas as configurações
     */
    public static function get_all() {
        $all_settings = [];

        foreach (array_keys(self::$defaults) as $key) {
            $all_settings[$key] = self::get($key);
        }

        return $all_settings;
    }

    /**
     * Resetar configurações para padrão
     */
    public static function reset_to_defaults() {
        $reset_count = 0;

        foreach (self::$defaults as $key => $default_value) {
            if (self::set($key, $default_value)) {
                $reset_count++;
            }
        }

        return $reset_count;
    }

    /**
     * Sanitizar valor baseado na chave
     */
    private static function sanitize_value($key, $value) {
        switch ($key) {
            case 'jwt_secret':
            case 'api_token':
                return sanitize_text_field($value);

            case 'api_url':
                return esc_url_raw($value);

            case 'cache_enabled':
            case 'debug_mode':
            case 'show_filters':
            case 'show_search':
            case 'show_pagination':
                return (bool) $value;

            case 'cache_duration':
            case 'items_per_page':
                return intval($value);

            case 'theme_color':
                return sanitize_hex_color($value);

            case 'template':
                $allowed = ['default', 'modern', 'compact'];
                return in_array($value, $allowed) ? $value : 'default';

            case 'width':
                if (strpos($value, '%') !== false || strpos($value, 'px') !== false) {
                    return sanitize_text_field($value);
                }
                return '100%';

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Verificar se uma configuração existe
     */
    public static function exists($key) {
        $option_key = self::OPTION_PREFIX . $key;
        return get_option($option_key) !== false;
    }

    /**
     * Deletar uma configuração
     */
    public static function delete($key) {
        $option_key = self::OPTION_PREFIX . $key;
        return delete_option($option_key);
    }

    /**
     * Obter configurações para shortcode
     */
    public static function get_shortcode_defaults() {
        return [
            'template' => self::get('template'),
            'items_per_page' => self::get('items_per_page'),
            'show_filters' => self::get('show_filters'),
            'show_search' => self::get('show_search'),
            'show_pagination' => self::get('show_pagination'),
            'theme_color' => self::get('theme_color'),
            'width' => self::get('width'),
        ];
    }

    /**
     * Validar configurações obrigatórias
     */
    public static function validate_required_settings() {
        $errors = [];

        if (empty(self::get('jwt_secret'))) {
            $errors[] = __('Chave secreta JWT não configurada', 'sm-student-control');
        }

        if (empty(self::get('api_url'))) {
            $errors[] = __('URL da API externa não configurada', 'sm-student-control');
        }

        return $errors;
    }
}