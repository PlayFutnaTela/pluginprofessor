<?php
/**
 * Acesso a dados do MasterStudy LMS
 * Queries otimizadas para estudantes e cursos
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Data {

    /**
     * Obter alunos de um professor
     */
    public static function get_professor_students($school_id, $professor_id, $args = []) {
        error_log('[SM-SC-DATA] 👥 Iniciando busca de estudantes - Professor ID: ' . $professor_id . ', School ID: ' . $school_id);

        // Verificar se WordPress e banco estão disponíveis
        if (!function_exists('wp_get_current_user') || !isset($GLOBALS['wpdb'])) {
            error_log('[SM-SC-DATA] ⚠️ WordPress ou banco de dados não disponíveis');
            return [];
        }

        global $wpdb;

        // Verificar se as tabelas do LMS existem
        $required_tables = [
            $wpdb->prefix . 'stm_lms_user_courses',
            $wpdb->prefix . 'stm_lms_courses'
        ];

        foreach ($required_tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
                error_log('[SM-SC-DATA] ⚠️ Tabela requerida não encontrada: ' . $table);
                return [];
            }
        }

        $defaults = [
            'search' => '',
            'course_id' => 0,
            'status' => '', // enrolled, completed, in_progress
            'orderby' => 'name',
            'order' => 'ASC',
            'limit' => SM_Student_Control_Settings::get('items_per_page'),
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        // Base query
        $query = "SELECT
            u.ID as student_id,
            u.user_login,
            u.user_email,
            u.display_name,
            um.meta_value as first_name,
            um2.meta_value as last_name,
            uc.course_id,
            c.title as course_title,
            uc.status as enrollment_status,
            uc.start_time,
            uc.end_time,
            uc.progress_percent
        FROM {$wpdb->prefix}stm_lms_user_courses uc
        INNER JOIN {$wpdb->users} u ON uc.user_id = u.ID
        LEFT JOIN {$wpdb->usermeta} um ON (u.ID = um.user_id AND um.meta_key = 'first_name')
        LEFT JOIN {$wpdb->usermeta} um2 ON (u.ID = um2.user_id AND um2.meta_key = 'last_name')
        INNER JOIN {$wpdb->prefix}stm_lms_courses c ON uc.course_id = c.course_id
        WHERE uc.status IN ('enrolled', 'completed', 'in_progress')";

        $params = [];

        // Filtros
        if (!empty($args['search'])) {
            $query .= " AND (u.display_name LIKE %s OR u.user_email LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if ($args['course_id'] > 0) {
            $query .= " AND uc.course_id = %d";
            $params[] = $args['course_id'];
        }

        if (!empty($args['status'])) {
            $query .= " AND uc.status = %s";
            $params[] = $args['status'];
        }

        // Aqui seria necessário filtrar por professor
        // Dependendo de como o LMS associa professores a alunos
        // Por enquanto, assumimos que todos os alunos são visíveis
        // TODO: Implementar filtro por professor baseado na estrutura do LMS

        // Ordenação
        $orderby_allowed = ['name', 'email', 'course_title', 'progress_percent', 'start_time'];
        $orderby = in_array($args['orderby'], $orderby_allowed) ? $args['orderby'] : 'name';

        if ($orderby === 'name') {
            $query .= " ORDER BY u.display_name {$args['order']}";
        } elseif ($orderby === 'email') {
            $query .= " ORDER BY u.user_email {$args['order']}";
        } elseif ($orderby === 'course_title') {
            $query .= " ORDER BY c.title {$args['order']}";
        } elseif ($orderby === 'progress_percent') {
            $query .= " ORDER BY uc.progress_percent {$args['order']}";
        } elseif ($orderby === 'start_time') {
            $query .= " ORDER BY uc.start_time {$args['order']}";
        }

        // Limite
        if ($args['limit'] > 0) {
            $query .= " LIMIT %d";
            $params[] = $args['limit'];

            if ($args['offset'] > 0) {
                $query .= " OFFSET %d";
                $params[] = $args['offset'];
            }
        }

        // Executar query
        if (!empty($params)) {
            $results = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
        } else {
            $results = $wpdb->get_results($query, ARRAY_A);
        }

        // Processar resultados
        $students = [];
        foreach ($results as $row) {
            $student_id = $row['student_id'];

            if (!isset($students[$student_id])) {
                $students[$student_id] = [
                    'id' => $student_id,
                    'name' => $row['display_name'],
                    'email' => $row['user_email'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'courses' => [],
                ];
            }

            $students[$student_id]['courses'][] = [
                'id' => $row['course_id'],
                'title' => $row['course_title'],
                'status' => $row['enrollment_status'],
                'progress' => $row['progress_percent'],
                'start_date' => $row['start_time'],
                'end_date' => $row['end_time'],
            ];
        }

        return array_values($students);
    }

    /**
     * Obter detalhes de um aluno específico
     */
    public static function get_student_details($student_id) {
        global $wpdb;

        // Verificar se aluno existe
        $student = get_userdata($student_id);
        if (!$student) {
            return null;
        }

        // Obter cursos do aluno
        $courses = $wpdb->get_results($wpdb->prepare("
            SELECT
                c.course_id,
                c.title,
                c.price,
                uc.status,
                uc.progress_percent,
                uc.start_time,
                uc.end_time,
                uc.current_lesson_id
            FROM {$wpdb->prefix}stm_lms_user_courses uc
            INNER JOIN {$wpdb->prefix}stm_lms_courses c ON uc.course_id = c.course_id
            WHERE uc.user_id = %d
            ORDER BY uc.start_time DESC
        ", $student_id), ARRAY_A);

        // Obter lições concluídas
        $lessons_completed = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}stm_lms_user_lessons
            WHERE user_id = %d AND status = 'completed'
        ", $student_id));

        // Obter quizzes realizados
        $quizzes = $wpdb->get_results($wpdb->prepare("
            SELECT
                q.title as quiz_title,
                uq.user_quiz_id,
                uq.progress,
                uq.status,
                uq.start_time,
                uq.end_time
            FROM {$wpdb->prefix}stm_lms_user_quizzes uq
            INNER JOIN {$wpdb->prefix}stm_lms_quizzes q ON uq.quiz_id = q.quiz_id
            WHERE uq.user_id = %d
            ORDER BY uq.start_time DESC
            LIMIT 10
        ", $student_id), ARRAY_A);

        return [
            'id' => $student_id,
            'name' => $student->display_name,
            'email' => $student->user_email,
            'first_name' => get_user_meta($student_id, 'first_name', true),
            'last_name' => get_user_meta($student_id, 'last_name', true),
            'registered_date' => $student->user_registered,
            'courses' => $courses,
            'lessons_completed' => $lessons_completed,
            'quizzes' => $quizzes,
            'total_courses' => count($courses),
            'completed_courses' => count(array_filter($courses, function($c) { return $c['status'] === 'completed'; })),
        ];
    }

    /**
     * Obter lista de cursos disponíveis
     */
    public static function get_available_courses() {
        global $wpdb;

        return $wpdb->get_results("
            SELECT course_id, title, price, status
            FROM {$wpdb->prefix}stm_lms_courses
            WHERE status = 'publish'
            ORDER BY title ASC
        ", ARRAY_A);
    }

    /**
     * Contar alunos de um professor
     */
    public static function count_professor_students($school_id, $professor_id) {
        global $wpdb;

        // TODO: Implementar filtro por professor
        return $wpdb->get_var("
            SELECT COUNT(DISTINCT uc.user_id)
            FROM {$wpdb->prefix}stm_lms_user_courses uc
            WHERE uc.status IN ('enrolled', 'completed', 'in_progress')
        ");
    }

    /**
     * Contar alunos ativos (últimos 30 dias)
     */
    public static function count_active_students($school_id, $professor_id, $days = 30) {
        global $wpdb;

        $date_limit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        // TODO: Implementar filtro por professor
        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT uc.user_id)
            FROM {$wpdb->prefix}stm_lms_user_courses uc
            WHERE uc.status IN ('enrolled', 'completed', 'in_progress')
            AND uc.start_time >= %s
        ", $date_limit));
    }

    /**
     * Exportar alunos para CSV
     */
    public static function export_students_csv() {
        $students = self::get_professor_students(0, 0, ['limit' => 0]); // Todos os alunos

        if (empty($students)) {
            return false;
        }

        // Criar arquivo CSV
        $filename = 'students-export-' . date('Y-m-d-H-i-s') . '.csv';
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;

        $file = fopen($file_path, 'w');

        // Headers
        fputcsv($file, [
            'ID',
            'Nome',
            'Email',
            'Primeiro Nome',
            'Último Nome',
            'Cursos Inscritos',
            'Cursos Completados',
            'Progresso Médio'
        ]);

        // Dados
        foreach ($students as $student) {
            $courses_count = count($student['courses']);
            $completed_count = count(array_filter($student['courses'], function($c) {
                return $c['status'] === 'completed';
            }));
            $avg_progress = $courses_count > 0 ?
                array_sum(array_column($student['courses'], 'progress')) / $courses_count : 0;

            fputcsv($file, [
                $student['id'],
                $student['name'],
                $student['email'],
                $student['first_name'],
                $student['last_name'],
                $courses_count,
                $completed_count,
                round($avg_progress, 1) . '%'
            ]);
        }

        fclose($file);

        return $upload_dir['url'] . '/' . $filename;
    }

    /**
     * Verificar se tabelas do LMS existem
     */
    public static function check_lms_tables() {
        global $wpdb;

        $tables = [
            $wpdb->prefix . 'stm_lms_user_courses',
            $wpdb->prefix . 'stm_lms_user_lessons',
            $wpdb->prefix . 'stm_lms_user_quizzes',
            $wpdb->prefix . 'stm_lms_courses',
        ];

        $missing = [];
        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                $missing[] = $table;
            }
        }

        return [
            'all_exist' => empty($missing),
            'missing_tables' => $missing,
            'existing_tables' => array_diff($tables, $missing),
        ];
    }
}