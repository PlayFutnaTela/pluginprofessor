<?php
/**
 * Integração com API externa
 * Comunicação segura com serviços externos
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_External_API {

    /**
     * URL base da API
     */
    private static $api_url = '';

    /**
     * Token de autenticação
     */
    private static $api_token = '';

    /**
     * Timeout das requisições
     */
    private static $timeout = 30;

    /**
     * Inicializar configurações da API
     */
    public static function init() {
        // Verificar se WordPress está totalmente carregado
        if (!function_exists('wp_get_current_user')) {
            error_log('[SM-SC-EXTERNAL-API] ⚠️ WordPress ainda não carregado, pulando inicialização da API externa');
            return;
        }

        self::$api_url = SM_Student_Control_Settings::get('api_url');
        self::$api_token = SM_Student_Control_Settings::get('api_token');
        self::$timeout = SM_Student_Control_Settings::get('api_timeout', 30);
    }

    /**
     * Fazer requisição GET para a API
     */
    public static function get($endpoint, $params = []) {
        return self::request('GET', $endpoint, $params);
    }

    /**
     * Fazer requisição POST para a API
     */
    public static function post($endpoint, $data = []) {
        return self::request('POST', $endpoint, [], $data);
    }

    /**
     * Fazer requisição PUT para a API
     */
    public static function put($endpoint, $data = []) {
        return self::request('PUT', $endpoint, [], $data);
    }

    /**
     * Fazer requisição DELETE para a API
     */
    public static function delete($endpoint) {
        return self::request('DELETE', $endpoint);
    }

    /**
     * Método genérico para requisições HTTP
     */
    private static function request($method, $endpoint, $params = [], $data = []) {
        // Verificar se API está configurada
        if (empty(self::$api_url) || empty(self::$api_token)) {
            return self::error_response(__('API externa não configurada', 'sm-student-control'));
        }

        // Construir URL completa
        $url = rtrim(self::$api_url, '/') . '/' . ltrim($endpoint, '/');

        // Adicionar parâmetros GET
        if (!empty($params) && $method === 'GET') {
            $url = add_query_arg($params, $url);
        }

        // Preparar argumentos da requisição
        $args = [
            'method' => $method,
            'timeout' => self::$timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . self::$api_token,
                'Content-Type' => 'application/json',
                'User-Agent' => 'SM Student Control Plugin/' . SM_STUDENT_CONTROL_VERSION,
            ],
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
        ];

        // Adicionar body para métodos que não são GET
        if (!empty($data) && $method !== 'GET') {
            $args['body'] = wp_json_encode($data);
        }

        // Fazer a requisição
        $response = wp_remote_request($url, $args);

        // Verificar erro de conexão
        if (is_wp_error($response)) {
            return self::error_response(
                __('Erro de conexão com API externa: ', 'sm-student-control') . $response->get_error_message()
            );
        }

        // Obter código de status
        $status_code = wp_remote_retrieve_response_code($response);

        // Obter corpo da resposta
        $body = wp_remote_retrieve_body($response);

        // Decodificar JSON
        $decoded_body = json_decode($body, true);

        // Verificar se é JSON válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            return self::error_response(__('Resposta inválida da API externa', 'sm-student-control'));
        }

        // Retornar resposta estruturada
        return [
            'success' => $status_code >= 200 && $status_code < 300,
            'status_code' => $status_code,
            'data' => $decoded_body,
            'raw_response' => $body,
        ];
    }

    /**
     * Testar conectividade com a API
     */
    public static function test_connection() {
        $response = self::get('test');

        if (!$response['success']) {
            return [
                'connected' => false,
                'error' => $response['data']['message'] ?? __('Erro desconhecido', 'sm-student-control'),
                'status_code' => $response['status_code'],
            ];
        }

        return [
            'connected' => true,
            'message' => __('Conexão estabelecida com sucesso', 'sm-student-control'),
            'status_code' => $response['status_code'],
        ];
    }

    /**
     * Obter dados de alunos da API externa
     */
    public static function get_students_from_api($school_id, $professor_id, $params = []) {
        $endpoint = 'schools/' . $school_id . '/professors/' . $professor_id . '/students';

        return self::get($endpoint, $params);
    }

    /**
     * Obter dados detalhados de um aluno
     */
    public static function get_student_details_from_api($student_id) {
        $endpoint = 'students/' . $student_id;

        return self::get($endpoint);
    }

    /**
     * Sincronizar dados de alunos
     */
    public static function sync_students_data($school_id, $professor_id) {
        $response = self::get_students_from_api($school_id, $professor_id);

        if (!$response['success']) {
            return $response;
        }

        // Processar dados recebidos
        $students_data = $response['data'];

        // Salvar no cache
        $cached = SM_Student_Control_Cache::set_professor_students_cache(
            $school_id,
            $professor_id,
            $students_data
        );

        return [
            'success' => $cached,
            'students_count' => count($students_data),
            'cached_at' => current_time('mysql'),
        ];
    }

    /**
     * Criar resposta de erro padronizada
     */
    private static function error_response($message) {
        return [
            'success' => false,
            'error' => $message,
            'status_code' => 0,
            'data' => null,
        ];
    }

    /**
     * Log de requisições (se debug estiver ativo)
     */
    private static function log_request($method, $endpoint, $response) {
        if (!SM_Student_Control_Settings::get('debug_mode')) {
            return;
        }

        $log_message = sprintf(
            '[%s] %s %s - Status: %d - Response: %s',
            current_time('Y-m-d H:i:s'),
            $method,
            $endpoint,
            $response['status_code'],
            substr(wp_json_encode($response['data']), 0, 500)
        );

        error_log($log_message);
    }
}

// REMOVIDO: SM_Student_Control_External_API::init();