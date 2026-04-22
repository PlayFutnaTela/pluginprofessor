<?php
/**
 * REST API
 *
 * API REST para o plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_REST_API {

    /**
     * Namespace da API
     */
    const API_NAMESPACE = 'sm-student-control/v1';

    /**
     * Inicializar API
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    /**
     * Registrar rotas da API
     */
    public static function register_routes() {
        // Rota para obter estudantes do professor
        register_rest_route(self::API_NAMESPACE, '/professor/students', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_professor_students'],
            'permission_callback' => [__CLASS__, 'check_professor_permissions'],
            'args' => [
                'search' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'course_id' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                ],
                'page' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1,
                ],
                'per_page' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 20,
                    'minimum' => 1,
                    'maximum' => 100,
                ],
            ],
        ]);

        // Rota para obter detalhes de um estudante
        register_rest_route(self::API_NAMESPACE, '/students/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_student_details'],
            'permission_callback' => [__CLASS__, 'check_student_access_permissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => [__CLASS__, 'validate_student_id'],
                ],
            ],
        ]);

        // Rota para atualizar cache de um estudante
        register_rest_route(self::API_NAMESPACE, '/students/(?P<id>\d+)/cache', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'refresh_student_cache'],
            'permission_callback' => [__CLASS__, 'check_student_access_permissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => [__CLASS__, 'validate_student_id'],
                ],
            ],
        ]);

        // Rota para obter estatísticas do professor
        register_rest_route(self::API_NAMESPACE, '/professor/stats', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_professor_stats'],
            'permission_callback' => [__CLASS__, 'check_professor_permissions'],
        ]);

        // Rota para obter cursos do professor
        register_rest_route(self::API_NAMESPACE, '/professor/courses', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_professor_courses'],
            'permission_callback' => [__CLASS__, 'check_professor_permissions'],
        ]);

        // Rota para exportar dados dos estudantes
        register_rest_route(self::API_NAMESPACE, '/professor/export', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'export_students_data'],
            'permission_callback' => [__CLASS__, 'check_professor_permissions'],
            'args' => [
                'format' => [
                    'required' => false,
                    'type' => 'string',
                    'enum' => ['csv', 'json'],
                    'default' => 'csv',
                ],
                'course_id' => [
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                ],
            ],
        ]);
    }

    /**
     * Verificar permissões do professor
     */
    public static function check_professor_permissions($request) {
        // Verificar se usuário está logado
        if (!is_user_logged_in()) {
            return new WP_Error('rest_forbidden', __('Você precisa estar logado.', 'sm-student-control'), ['status' => 401]);
        }

        // Verificar JWT
        $professor_data = SM_Student_Control_JWT::get_professor_data();
        if (!$professor_data) {
            return new WP_Error('rest_forbidden', __('Token de acesso inválido.', 'sm-student-control'), ['status' => 403]);
        }

        return true;
    }

    /**
     * Verificar permissões de acesso a estudante específico
     */
    public static function check_student_access_permissions($request) {
        $student_id = $request->get_param('id');

        // Verificar permissões básicas do professor
        $permission_check = self::check_professor_permissions($request);
        if (is_wp_error($permission_check)) {
            return $permission_check;
        }

        // Verificar se o professor tem acesso a este estudante
        if (!SM_Student_Control_Professor_Students::professor_has_access_to_student(
            get_current_user_id(),
            $student_id
        )) {
            return new WP_Error('rest_forbidden', __('Acesso negado a este estudante.', 'sm-student-control'), ['status' => 403]);
        }

        return true;
    }

    /**
     * Validar ID do estudante
     */
    public static function validate_student_id($value, $request, $param) {
        if (!is_numeric($value) || $value <= 0) {
            return new WP_Error('rest_invalid_param', __('ID do estudante deve ser um número positivo.', 'sm-student-control'));
        }

        // Verificar se estudante existe
        if (!get_user_by('id', $value)) {
            return new WP_Error('rest_invalid_param', __('Estudante não encontrado.', 'sm-student-control'));
        }

        return true;
    }

    /**
     * Obter estudantes do professor
     */
    public static function get_professor_students($request) {
        try {
            $professor_data = SM_Student_Control_JWT::get_professor_data();

            $args = [
                'search' => $request->get_param('search'),
                'course_id' => $request->get_param('course_id'),
                'page' => $request->get_param('page'),
                'per_page' => $request->get_param('per_page'),
            ];

            $students = SM_Student_Control_Professor_Students::get_professor_students(
                $professor_data['school_id'],
                $professor_data['professor_id'],
                $args
            );

            $total_students = SM_Student_Control_Professor_Students::count_professor_students(
                $professor_data['school_id'],
                $professor_data['professor_id']
            );

            $response = [
                'students' => $students,
                'total' => $total_students,
                'page' => $args['page'],
                'per_page' => $args['per_page'],
                'total_pages' => ceil($total_students / $args['per_page']),
            ];

            return new WP_REST_Response($response, 200);

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API get_professor_students: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Obter detalhes de um estudante
     */
    public static function get_student_details($request) {
        try {
            $student_id = $request->get_param('id');

            $student = SM_Student_Control_Professor_Students::get_student_details($student_id);

            if (!$student) {
                return new WP_Error('rest_not_found', __('Estudante não encontrado.', 'sm-student-control'), ['status' => 404]);
            }

            return new WP_REST_Response($student, 200);

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API get_student_details: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Atualizar cache de um estudante
     */
    public static function refresh_student_cache($request) {
        try {
            $student_id = $request->get_param('id');

            $result = SM_Student_Control_Cache::refresh_student_cache($student_id);

            if ($result) {
                return new WP_REST_Response([
                    'success' => true,
                    'message' => __('Cache atualizado com sucesso.', 'sm-student-control'),
                ], 200);
            } else {
                return new WP_Error('rest_internal_error', __('Erro ao atualizar cache.', 'sm-student-control'), ['status' => 500]);
            }

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API refresh_student_cache: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Obter estatísticas do professor
     */
    public static function get_professor_stats($request) {
        try {
            $professor_data = SM_Student_Control_JWT::get_professor_data();

            $stats = SM_Student_Control_Professor_Students::get_professor_stats(
                $professor_data['school_id'],
                $professor_data['professor_id']
            );

            return new WP_REST_Response($stats, 200);

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API get_professor_stats: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Obter cursos do professor
     */
    public static function get_professor_courses($request) {
        try {
            $professor_data = SM_Student_Control_JWT::get_professor_data();

            $courses = SM_Student_Control_Professor_Students::get_professor_courses(
                $professor_data['school_id'],
                $professor_data['professor_id']
            );

            return new WP_REST_Response($courses, 200);

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API get_professor_courses: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Exportar dados dos estudantes
     */
    public static function export_students_data($request) {
        try {
            $professor_data = SM_Student_Control_JWT::get_professor_data();
            $format = $request->get_param('format');
            $course_id = $request->get_param('course_id');

            $args = [
                'course_id' => $course_id,
                'per_page' => -1, // Todos os estudantes
            ];

            $students = SM_Student_Control_Professor_Students::get_professor_students(
                $professor_data['school_id'],
                $professor_data['professor_id'],
                $args
            );

            if ($format === 'json') {
                $filename = 'estudantes-professor-' . date('Y-m-d') . '.json';
                $data = json_encode($students, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                return new WP_REST_Response($data, 200, [
                    'Content-Type' => 'application/json',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            } else {
                // CSV
                $filename = 'estudantes-professor-' . date('Y-m-d') . '.csv';
                $headers = [
                    'ID',
                    'Nome',
                    'Email',
                    'Cursos',
                    'Progresso Médio (%)',
                    'Último Acesso',
                    'Data de Cadastro',
                ];

                $rows = [];
                foreach ($students as $student) {
                    $rows[] = [
                        $student['id'],
                        $student['display_name'],
                        $student['user_email'],
                        $student['courses_count'],
                        $student['progress'],
                        $student['last_login'] ? SM_Student_Control_Helpers::format_date($student['last_login']) : '',
                        $student['registration_date'] ? SM_Student_Control_Helpers::format_date($student['registration_date']) : '',
                    ];
                }

                $csv = SM_Student_Control_Helpers::array_to_csv($rows, $headers);

                return new WP_REST_Response($csv, 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

        } catch (Exception $e) {
            SM_Student_Control_Helpers::debug_log('Erro na API export_students_data: ' . $e->getMessage());
            return new WP_Error('rest_internal_error', __('Erro interno do servidor.', 'sm-student-control'), ['status' => 500]);
        }
    }

    /**
     * Obter URL base da API
     */
    public static function get_api_url($endpoint = '') {
        return get_rest_url(null, self::API_NAMESPACE . '/' . ltrim($endpoint, '/'));
    }

    /**
     * Fazer requisição para a API externa
     */
    public static function make_external_request($endpoint, $method = 'GET', $data = []) {
        $base_url = SM_Student_Control_Settings::get_setting('api_base_url');
        $api_key = SM_Student_Control_Settings::get_setting('api_key');

        if (empty($base_url) || empty($api_key)) {
            throw new Exception(__('Configurações da API externa não encontradas.', 'sm-student-control'));
        }

        $url = rtrim($base_url, '/') . '/' . ltrim($endpoint, '/');

        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $args['body'] = json_encode($data);
        } elseif (!empty($data) && $method === 'GET') {
            $url = add_query_arg($data, $url);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code >= 400) {
            throw new Exception(__('Erro na API externa: ', 'sm-student-control') . $status_code);
        }

        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(__('Resposta inválida da API externa.', 'sm-student-control'));
        }

        return $decoded;
    }
}

// REMOVIDO: SM_Student_Control_REST_API::init();