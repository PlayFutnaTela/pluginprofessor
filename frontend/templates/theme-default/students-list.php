<?php
/**
 * Template: Lista de Estudantes
 *
 * Template para renderizar a lista de estudantes
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verificar se temos dados do professor
if (!isset($professor_data) || !isset($atts)) {
    return;
}

// Obter estudantes
$args = [
    'search' => '',
    'course_id' => 0,
    'page' => 1,
    'per_page' => intval($atts['per_page'] ?: 20),
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
?>

<div class="sm-sc-students-list-container">
    <?php if (empty($students)): ?>
        <div class="sm-sc-no-students">
            <div class="sm-sc-no-students-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <h3><?php _e('Nenhum aluno encontrado', 'sm-student-control'); ?></h3>
            <p><?php _e('Não há alunos associados a este professor no momento.', 'sm-student-control'); ?></p>
        </div>
    <?php else: ?>
        <div class="sm-sc-students-header">
            <div class="sm-sc-students-count">
                <?php printf(
                    _n(
                        '%d aluno encontrado',
                        '%d alunos encontrados',
                        $total_students,
                        'sm-student-control'
                    ),
                    number_format_i18n($total_students)
                ); ?>
            </div>
        </div>

        <div class="sm-sc-students-grid">
            <?php foreach ($students as $student): ?>
                <div class="sm-sc-student-card" data-student-id="<?php echo esc_attr($student['id']); ?>">
                    <div class="sm-sc-student-card-header">
                        <div class="sm-sc-student-avatar">
                            <?php if (!empty($student['avatar'])): ?>
                                <img src="<?php echo esc_url($student['avatar']); ?>"
                                     alt="<?php echo esc_attr($student['display_name']); ?>"
                                     class="sm-sc-avatar">
                            <?php else: ?>
                                <div class="sm-sc-avatar-placeholder">
                                    <?php echo esc_html(SM_Student_Control_Helpers::get_initials($student['display_name'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="sm-sc-student-basic-info">
                            <h4 class="sm-sc-student-name">
                                <a href="#" class="sm-sc-student-link" data-student-id="<?php echo esc_attr($student['id']); ?>">
                                    <?php echo esc_html($student['display_name']); ?>
                                </a>
                            </h4>
                            <div class="sm-sc-student-email"><?php echo esc_html($student['user_email']); ?></div>
                        </div>
                    </div>

                    <div class="sm-sc-student-card-body">
                        <div class="sm-sc-student-progress">
                            <div class="sm-sc-progress-info">
                                <span class="sm-sc-progress-label"><?php _e('Progresso Geral:', 'sm-student-control'); ?></span>
                                <span class="sm-sc-progress-value"><?php echo esc_html($student['progress']); ?>%</span>
                            </div>
                            <div class="sm-sc-progress-bar">
                                <div class="sm-sc-progress-fill" style="width: <?php echo esc_attr($student['progress']); ?>%"></div>
                            </div>
                        </div>

                        <div class="sm-sc-student-courses">
                            <div class="sm-sc-courses-count">
                                <?php printf(
                                    _n(
                                        '%d curso',
                                        '%d cursos',
                                        $student['courses_count'],
                                        'sm-student-control'
                                    ),
                                    $student['courses_count']
                                ); ?>
                            </div>
                            <?php if (!empty($student['current_course'])): ?>
                                <div class="sm-sc-current-course">
                                    <?php _e('Atualmente em:', 'sm-student-control'); ?>
                                    <?php echo esc_html($student['current_course']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($student['last_login'])): ?>
                            <div class="sm-sc-student-last-activity">
                                <span class="sm-sc-activity-label"><?php _e('Último acesso:', 'sm-student-control'); ?></span>
                                <span class="sm-sc-activity-date">
                                    <?php echo esc_html(SM_Student_Control_Helpers::format_date($student['last_login'])); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="sm-sc-student-card-footer">
                        <div class="sm-sc-student-actions">
                            <button type="button"
                                    class="button small sm-sc-view-details"
                                    data-student-id="<?php echo esc_attr($student['id']); ?>"
                                    title="<?php esc_attr_e('Ver detalhes completos', 'sm-student-control'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                <?php _e('Detalhes', 'sm-student-control'); ?>
                            </button>

                            <button type="button"
                                    class="button small secondary sm-sc-refresh-cache"
                                    data-student-id="<?php echo esc_attr($student['id']); ?>"
                                    title="<?php esc_attr_e('Atualizar dados do cache', 'sm-student-control'); ?>">
                                <span class="dashicons dashicons-update"></span>
                                <?php _e('Atualizar', 'sm-student-control'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_students > $args['per_page']): ?>
            <div class="sm-sc-pagination">
                <?php
                $total_pages = ceil($total_students / $args['per_page']);
                $current_page = 1;
                ?>

                <button type="button"
                        class="button secondary sm-sc-prev-page"
                        <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                    <?php _e('Anterior', 'sm-student-control'); ?>
                </button>

                <span class="sm-sc-page-info">
                    <?php printf(
                        __('Página %d de %d', 'sm-student-control'),
                        $current_page,
                        $total_pages
                    ); ?>
                </span>

                <button type="button"
                        class="button secondary sm-sc-next-page"
                        <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>>
                    <?php _e('Próximo', 'sm-student-control'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>