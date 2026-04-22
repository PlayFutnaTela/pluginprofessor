<?php
/**
 * Gerenciamento de alunos por professor
 * Lógica de negócio para associação professor-aluno
 */

if (!defined('ABSPATH')) {
    exit;
}

class SM_Student_Control_Professor_Students {

    /**
     * Obter alunos de um professor específico
     */
    public static function get_professor_students($school_id, $professor_id, $args = []) {
        // Verificar cache primeiro
        $cached = SM_Student_Control_Cache::get_professor_students_cache($school_id, $professor_id);

        if (!empty($cached)) {
            // Aplicar filtros em cache se necessário
            return self::filter_cached_students($cached, $args);
        }

        // Obter da API externa se disponível
        $api_response = SM_Student_Control_External_API::get_students_from_api($school_id, $professor_id, $args);

        if ($api_response['success']) {
            $students = $api_response['data'];

            // Salvar no cache
            SM_Student_Control_Cache::set_professor_students_cache($school_id, $professor_id, $students);

            return self::filter_students($students, $args);
        }

        // Fallback para dados do LMS
        $students = SM_Student_Control_Data::get_professor_students($school_id, $professor_id, $args);

        // Salvar no cache
        SM_Student_Control_Cache::set_professor_students_cache($school_id, $professor_id, $students);

        return $students;
    }

    /**
     * Obter detalhes de um aluno específico
     */
    public static function get_student_details($student_id) {
        // Verificar cache
        $cached = SM_Student_Control_Cache::get_student_details_cache($student_id);

        if ($cached) {
            return $cached;
        }

        // Obter da API externa
        $api_response = SM_Student_Control_External_API::get_student_details_from_api($student_id);

        if ($api_response['success']) {
            $student = $api_response['data'];

            // Salvar no cache
            SM_Student_Control_Cache::set_student_details_cache($student_id, $student);

            return $student;
        }

        // Fallback para dados do LMS
        $student = SM_Student_Control_Data::get_student_details($student_id);

        if ($student) {
            // Salvar no cache
            SM_Student_Control_Cache::set_student_details_cache($student_id, $student);
        }

        return $student;
    }

    /**
     * Contar alunos de um professor
     */
    public static function count_professor_students($school_id, $professor_id) {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);
        return count($students);
    }

    /**
     * Contar alunos ativos (com atividade recente)
     */
    public static function count_active_students($school_id, $professor_id, $days = 30) {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        $active_count = 0;
        $cutoff_date = strtotime("-{$days} days");

        foreach ($students as $student) {
            // Verificar se tem cursos com atividade recente
            foreach ($student['courses'] as $course) {
                if (strtotime($course['start_date']) > $cutoff_date) {
                    $active_count++;
                    break;
                }
            }
        }

        return $active_count;
    }

    /**
     * Obter estatísticas do professor
     */
    public static function get_professor_stats($school_id, $professor_id) {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        $stats = [
            'total_students' => count($students),
            'active_students' => 0,
            'completed_courses' => 0,
            'in_progress_courses' => 0,
            'total_courses' => 0,
            'average_progress' => 0,
        ];

        $total_progress = 0;
        $progress_count = 0;

        foreach ($students as $student) {
            $has_recent_activity = false;

            foreach ($student['courses'] as $course) {
                $stats['total_courses']++;

                if ($course['status'] === 'completed') {
                    $stats['completed_courses']++;
                } elseif ($course['status'] === 'in_progress') {
                    $stats['in_progress_courses']++;
                }

                // Verificar atividade recente (últimos 30 dias)
                if (strtotime($course['start_date']) > strtotime('-30 days')) {
                    $has_recent_activity = true;
                }

                // Calcular progresso médio
                if (isset($course['progress'])) {
                    $total_progress += $course['progress'];
                    $progress_count++;
                }
            }

            if ($has_recent_activity) {
                $stats['active_students']++;
            }
        }

        if ($progress_count > 0) {
            $stats['average_progress'] = round($total_progress / $progress_count, 1);
        }

        return $stats;
    }

    /**
     * Filtrar alunos baseado em argumentos
     */
    private static function filter_students($students, $args) {
        $defaults = [
            'search' => '',
            'course_id' => 0,
            'status' => '',
            'limit' => SM_Student_Control_Settings::get('items_per_page'),
            'offset' => 0,
        ];

        $args = wp_parse_args($args, $defaults);

        // Filtrar por busca
        if (!empty($args['search'])) {
            $students = array_filter($students, function($student) use ($args) {
                $search_lower = strtolower($args['search']);
                return strpos(strtolower($student['name']), $search_lower) !== false ||
                       strpos(strtolower($student['email']), $search_lower) !== false;
            });
        }

        // Filtrar por curso
        if ($args['course_id'] > 0) {
            $students = array_filter($students, function($student) use ($args) {
                foreach ($student['courses'] as $course) {
                    if ($course['id'] == $args['course_id']) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Filtrar por status
        if (!empty($args['status'])) {
            $students = array_filter($students, function($student) use ($args) {
                foreach ($student['courses'] as $course) {
                    if ($course['status'] === $args['status']) {
                        return true;
                    }
                }
                return false;
            });
        }

        // Aplicar limite e offset
        if ($args['limit'] > 0) {
            $students = array_slice($students, $args['offset'], $args['limit']);
        }

        return array_values($students);
    }

    /**
     * Filtrar alunos em cache (versão otimizada)
     */
    private static function filter_cached_students($cached_students, $args) {
        // Para dados em cache, aplicar filtros em memória
        return self::filter_students($cached_students, $args);
    }

    /**
     * Verificar se professor tem acesso a um aluno
     */
    public static function professor_has_access_to_student($professor_id, $student_id) {
        // Obter dados do professor
        $professor_data = SM_Student_Control_JWT::get_professor_data();

        if (!$professor_data) {
            return false;
        }

        // Obter alunos do professor
        $students = self::get_professor_students(
            $professor_data['school_id'],
            $professor_data['professor_id'],
            ['limit' => 0]
        );

        // Verificar se aluno está na lista
        foreach ($students as $student) {
            if ($student['id'] == $student_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obter alunos por curso
     */
    public static function get_students_by_course($school_id, $professor_id, $course_id) {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        return array_filter($students, function($student) use ($course_id) {
            foreach ($student['courses'] as $course) {
                if ($course['id'] == $course_id) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Obter alunos com baixo desempenho
     */
    public static function get_students_with_low_performance($school_id, $professor_id, $threshold = 50) {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        return array_filter($students, function($student) use ($threshold) {
            $avg_progress = 0;
            $course_count = count($student['courses']);

            if ($course_count === 0) {
                return false;
            }

            foreach ($student['courses'] as $course) {
                $avg_progress += $course['progress'];
            }

            $avg_progress /= $course_count;

            return $avg_progress < $threshold;
        });
    }

    /**
     * Exportar dados dos alunos
     */
    public static function export_students_data($school_id, $professor_id, $format = 'csv') {
        $students = self::get_professor_students($school_id, $professor_id, ['limit' => 0]);

        if ($format === 'csv') {
            return SM_Student_Control_Data::export_students_csv();
        }

        // Outros formatos podem ser implementados
        return false;
    }
}