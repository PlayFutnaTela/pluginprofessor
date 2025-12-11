<?php
/**
 * External API Integration Class
 *
 * Responsável por integração com a API externa de classes (pmais-classes-api)
 *
 * @since      1.0.0
 * @package    SM_Student_Control
 * @subpackage SM_Student_Control/includes
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SM_Student_Control_External_API {

    /**
     * API base URL
     *
     * @var string
     */
    private static $api_base_url = 'https://pmais-classes-api-staging.p4ed.com/api';

    /**
     * Authorization token
     *
     * @var string
     */
    private static $auth_token = '';

    /**
     * Set the authorization token
     *
     * @param string $token Bearer token for API authentication
     */
    public static function set_auth_token($token) {
        self::$auth_token = $token;
    }

    /**
     * Get the authorization token from WordPress options
     *
     * @return string Authorization token
     */
    public static function get_auth_token() {
        if (empty(self::$auth_token)) {
            // Try to get from options if not set
            self::$auth_token = get_option('sm_student_control_api_token', '');
        }
        return self::$auth_token;
    }

    /**
     * Fetch classes (turmas) for a teacher
     *
     * @param int    $school_id   School ID
     * @param int    $user_id     Teacher user ID
     * @param int    $type        Type parameter (usually 1)
     * @param int    $role_id     Role ID (usually 1 for teacher)
     *
     * @return array|WP_Error Array of classes or WP_Error on failure
     */
    public static function get_classes_by_user($school_id, $user_id, $type = 1, $role_id = 1) {
        $token = self::get_auth_token();

        if (empty($token)) {
            return new WP_Error(
                'missing_auth_token',
                __('Token de autenticação não configurado', 'sm-student-control')
            );
        }

        $url = self::$api_base_url . '/class/classesByUser?' . http_build_query([
            'schoolId' => absint($school_id),
            'userId' => absint($user_id),
            'type' => absint($type),
            'roleId' => absint($role_id),
        ]);

        $response = wp_remote_get(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . sanitize_text_field($token),
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'sslverify' => true,
            ]
        );

        return self::process_response($response);
    }

    /**
     * Fetch students assigned to a class
     *
     * @param int    $class_id Class ID
     * @param int    $limit    Number of students per page (default 20)
     * @param int    $page     Page number (default 1)
     *
     * @return array|WP_Error Array of students or WP_Error on failure
     */
    public static function get_assigned_students($class_id, $limit = 20, $page = 1) {
        $token = self::get_auth_token();

        if (empty($token)) {
            return new WP_Error(
                'missing_auth_token',
                __('Token de autenticação não configurado', 'sm-student-control')
            );
        }

        $url = self::$api_base_url . '/studentclass/assignedStudents/' . absint($class_id) . '?' . http_build_query([
            'limit' => absint($limit),
            'page' => absint($page),
        ]);

        $response = wp_remote_get(
            $url,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . sanitize_text_field($token),
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'sslverify' => true,
            ]
        );

        return self::process_response($response);
    }

    /**
     * Process API response
     *
     * @param array|WP_Error $response HTTP response
     *
     * @return array|WP_Error Decoded response or WP_Error
     */
    private static function process_response($response) {
        if (is_wp_error($response)) {
            return $response;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($http_code < 200 || $http_code >= 300) {
            return new WP_Error(
                'api_error',
                sprintf(
                    __('Erro na API (HTTP %d): %s', 'sm-student-control'),
                    $http_code,
                    $body
                )
            );
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'json_decode_error',
                __('Erro ao decodificar resposta JSON da API', 'sm-student-control')
            );
        }

        return $data;
    }

    /**
     * Cache classes data
     *
     * @param int   $school_id School ID
     * @param int   $user_id   Teacher user ID
     * @param array $data      Classes data
     * @param int   $expiration Cache expiration in seconds (default 1 hour)
     *
     * @return bool
     */
    public static function cache_classes($school_id, $user_id, $data, $expiration = 3600) {
        $cache_key = 'sm_student_control_classes_' . absint($school_id) . '_' . absint($user_id);
        return set_transient($cache_key, $data, $expiration);
    }

    /**
     * Get cached classes data
     *
     * @param int $school_id School ID
     * @param int $user_id   Teacher user ID
     *
     * @return mixed Cached data or false
     */
    public static function get_cached_classes($school_id, $user_id) {
        $cache_key = 'sm_student_control_classes_' . absint($school_id) . '_' . absint($user_id);
        return get_transient($cache_key);
    }

    /**
     * Cache students data for a class
     *
     * @param int   $class_id   Class ID
     * @param array $data       Students data
     * @param int   $expiration Cache expiration in seconds (default 1 hour)
     *
     * @return bool
     */
    public static function cache_class_students($class_id, $data, $expiration = 3600) {
        $cache_key = 'sm_student_control_students_' . absint($class_id);
        return set_transient($cache_key, $data, $expiration);
    }

    /**
     * Get cached students data for a class
     *
     * @param int $class_id Class ID
     *
     * @return mixed Cached data or false
     */
    public static function get_cached_class_students($class_id) {
        $cache_key = 'sm_student_control_students_' . absint($class_id);
        return get_transient($cache_key);
    }
}
