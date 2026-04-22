<?php
/**
 * Sistema de cache inteligente
 * Cache de dados de alunos e estatísticas
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Cache {

    /**
     * Prefixo para chaves de cache
     */
    const CACHE_PREFIX = 'sm_sc_';

    /**
     * Duração padrão do cache (segundos)
     */
    private static $default_duration = 3600; // 1 hora

    /**
     * Inicializar cache
     */
    public static function init() {
        // Verificar se WordPress está totalmente carregado
        if (!function_exists('wp_get_current_user')) {
            error_log('[SM-SC-CACHE] ⚠️ WordPress ainda não carregado, pulando inicialização do cache');
            return;
        }

        error_log('[SM-SC-CACHE] 💾 Inicializando sistema de cache');

        self::$default_duration = SM_Student_Control_Settings::get('cache_duration', 3600);

        // Hook para limpeza diária
        add_action('sm_sc_daily_cache_refresh', [__CLASS__, 'refresh_all_cache']);
    }

    /**
     * Verificar se cache está habilitado
     */
    public static function is_enabled() {
        return SM_Student_Control_Settings::get('cache_enabled', true);
    }

    /**
     * Obter dados do cache
     */
    public static function get($key, $default = null) {
        if (!self::is_enabled()) {
            return $default;
        }

        $cache_key = self::CACHE_PREFIX . $key;
        $cached = get_transient($cache_key);

        if ($cached === false) {
            return $default;
        }

        return $cached;
    }

    /**
     * Salvar dados no cache
     */
    public static function set($key, $value, $duration = null) {
        if (!self::is_enabled()) {
            return false;
        }

        $cache_key = self::CACHE_PREFIX . $key;
        $duration = $duration ?: self::$default_duration;

        return set_transient($cache_key, $value, $duration);
    }

    /**
     * Deletar item do cache
     */
    public static function delete($key) {
        $cache_key = self::CACHE_PREFIX . $key;
        return delete_transient($cache_key);
    }

    /**
     * Limpar todo o cache do plugin
     */
    public static function clear_all_cache() {
        global $wpdb;

        $prefix = self::CACHE_PREFIX;
        $option_names = $wpdb->get_col($wpdb->prepare("
            SELECT option_name
            FROM {$wpdb->options}
            WHERE option_name LIKE %s
        ", '_transient_' . $prefix . '%'));

        $cleared = 0;
        foreach ($option_names as $option_name) {
            $transient_name = str_replace('_transient_', '', $option_name);
            if (delete_transient($transient_name)) {
                $cleared++;
            }
        }

        // Log da limpeza
        if (SM_Student_Control_Settings::get('debug_mode')) {
            error_log(sprintf(
                '[SM Student Control] Cache cleared: %d items removed',
                $cleared
            ));
        }

        return $cleared;
    }

    /**
     * Obter cache de alunos de um professor
     */
    public static function get_professor_students_cache($school_id, $professor_id) {
        $key = 'professor_students_' . $school_id . '_' . $professor_id;
        return self::get($key, []);
    }

    /**
     * Salvar cache de alunos de um professor
     */
    public static function set_professor_students_cache($school_id, $professor_id, $students_data) {
        $key = 'professor_students_' . $school_id . '_' . $professor_id;
        return self::set($key, $students_data);
    }

    /**
     * Obter cache de detalhes de um aluno
     */
    public static function get_student_details_cache($student_id) {
        $key = 'student_details_' . $student_id;
        return self::get($key);
    }

    /**
     * Salvar cache de detalhes de um aluno
     */
    public static function set_student_details_cache($student_id, $student_data) {
        $key = 'student_details_' . $student_id;
        return self::set($key, $student_data);
    }

    /**
     * Atualizar cache de um aluno específico
     */
    public static function refresh_student_cache($student_id) {
        // Obter dados atualizados
        $student_data = SM_Student_Control_Data::get_student_details($student_id);

        if (!$student_data) {
            return false;
        }

        // Salvar no cache
        $cached = self::set_student_details_cache($student_id, $student_data);

        // Hook para ações após atualização
        do_action('sm_sc_student_cache_updated', $student_id, $student_data);

        return $cached;
    }

    /**
     * Atualizar cache de todos os alunos de um professor
     */
    public static function refresh_professor_cache($school_id, $professor_id) {
        // Obter dados atualizados
        $students_data = SM_Student_Control_Data::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        if (empty($students_data)) {
            return false;
        }

        // Salvar no cache
        $cached = self::set_professor_students_cache($school_id, $professor_id, $students_data);

        // Hook para ações após atualização
        do_action('sm_sc_professor_cache_updated', $school_id, $professor_id, $students_data);

        return $cached;
    }

    /**
     * Atualizar todo o cache do sistema
     */
    public static function refresh_all_cache() {
        $start_time = microtime(true);

        // Limpar cache existente
        $cleared = self::clear_all_cache();

        // Obter lista de professores (simplificado)
        // TODO: Implementar obtenção real de professores
        $professors = self::get_professors_list();

        $refreshed_professors = 0;
        $total_students = 0;

        foreach ($professors as $professor) {
            $refreshed = self::refresh_professor_cache($professor['school_id'], $professor['professor_id']);
            if ($refreshed) {
                $refreshed_professors++;
                $students = self::get_professor_students_cache($professor['school_id'], $professor['professor_id']);
                $total_students += count($students);
            }
        }

        $end_time = microtime(true);
        $duration = round($end_time - $start_time, 2);

        // Log do resultado
        if (SM_Student_Control_Settings::get('debug_mode')) {
            error_log(sprintf(
                '[SM Student Control] Full cache refresh completed in %s seconds. Professors: %d, Students: %d, Items cleared: %d',
                $duration,
                $refreshed_professors,
                $total_students,
                $cleared
            ));
        }

        // Hook para ações após atualização completa
        do_action('sm_sc_full_cache_refresh_completed', [
            'duration' => $duration,
            'professors_refreshed' => $refreshed_professors,
            'total_students' => $total_students,
            'items_cleared' => $cleared,
        ]);

        return [
            'success' => true,
            'duration' => $duration,
            'professors_refreshed' => $refreshed_professors,
            'total_students' => $total_students,
            'items_cleared' => $cleared,
        ];
    }

    /**
     * Obter lista de professores (simplificado)
     * TODO: Implementar obtenção real baseada na estrutura do LMS
     */
    private static function get_professors_list() {
        // Por enquanto, retornar dados mock
        // Deve ser implementado baseado em como professores são identificados no LMS
        return [
            ['school_id' => 1, 'professor_id' => 1],
        ];
    }

    /**
     * Obter estatísticas do cache
     */
    public static function get_cache_stats() {
        global $wpdb;

        $prefix = '_transient_' . self::CACHE_PREFIX;

        $stats = $wpdb->get_row($wpdb->prepare("
            SELECT
                COUNT(*) as total_items,
                MIN(option_value) as oldest_item,
                MAX(option_value) as newest_item
            FROM {$wpdb->options}
            WHERE option_name LIKE %s
        ", $prefix . '%'));

        $total_size = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(LENGTH(option_value)) as total_size
            FROM {$wpdb->options}
            WHERE option_name LIKE %s
        ", $prefix . '%'));

        return [
            'enabled' => self::is_enabled(),
            'total_items' => $stats->total_items ?? 0,
            'total_size_bytes' => $total_size ?? 0,
            'total_size_mb' => round(($total_size ?? 0) / 1024 / 1024, 2),
            'default_duration' => self::$default_duration,
            'duration_hours' => round(self::$default_duration / 3600, 1),
        ];
    }

    /**
     * Verificar se cache de aluno está expirado
     */
    public static function is_student_cache_expired($student_id) {
        $key = 'student_details_' . $student_id;
        $cache_key = self::CACHE_PREFIX . $key;

        return get_transient($cache_key) === false;
    }

    /**
     * Forçar atualização de cache (ignorar configurações)
     */
    public static function force_refresh_all_cache() {
        $enabled = self::is_enabled();
        SM_Student_Control_Settings::set('cache_enabled', true);

        $result = self::refresh_all_cache();

        // Restaurar configuração original
        SM_Student_Control_Settings::set('cache_enabled', $enabled);

        return $result;
    }
}

// REMOVIDO: SM_Student_Control_Cache::init();